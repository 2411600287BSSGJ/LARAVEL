<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\PurchaseOrderNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    /**
     * Display listing of purchase orders with status, product, and supplier filters.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', PurchaseOrder::class);

        $query = PurchaseOrder::with(['product', 'items.product', 'supplier', 'creator']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"))
                  ->orWhereHas('items.product', fn ($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
            });
        }

        $purchaseOrders = $query->latest()->paginate(15)->withQueryString();
        $suppliers = Supplier::orderBy('name')->get();

        return view('purchase-orders.index', compact('purchaseOrders', 'suppliers'));
    }

    /**
     * Show form for creating a new purchase order manually.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', PurchaseOrder::class);

        $products = Product::with('supplier')->orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $selectedProductId = $request->input('product_id');
        $selectedSupplierId = $request->input('supplier_id');

        return view('purchase-orders.create', compact('products', 'suppliers', 'selectedProductId', 'selectedSupplierId'));
    }

    /**
     * Store a newly created purchase order.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', PurchaseOrder::class);

        if (!$request->has('items') && $request->filled('product_id') && $request->filled('quantity')) {
            $request->merge([
                'items' => [[
                    'product_id' => $request->input('product_id'),
                    'quantity' => $request->input('quantity'),
                ]],
            ]);
        }

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.product_id' => ['required', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $productIds = collect($validated['items'])->pluck('product_id');
        $products = Product::query()->whereIn('id', $productIds)->get()->keyBy('id');
        $firstProduct = $products->get($validated['items'][0]['product_id']);
        $supplierIds = $products->pluck('supplier_id')->filter()->unique()->values();
        $supplierId = $validated['supplier_id'] ?? ($supplierIds->count() === 1 ? $supplierIds->first() : null);

        if (!isset($validated['supplier_id']) && $supplierIds->count() > 1) {
            return back()->withErrors([
                'supplier_id' => 'Select one supplier when the products have different primary suppliers.',
            ])->withInput();
        }

        $subtotal = round(collect($validated['items'])->sum(
            fn (array $item) => (int) $item['quantity'] * (float) $products->get($item['product_id'])->unit_price
        ), 2);

        $order = DB::transaction(function () use ($validated, $firstProduct, $supplierId, $products, $subtotal): PurchaseOrder {
            $firstItem = $validated['items'][0];
            $order = PurchaseOrder::create([
                'reference' => PurchaseOrder::generateReference(),
                'product_id' => $firstProduct->id,
                'supplier_id' => $supplierId,
                'quantity' => (int) $firstItem['quantity'],
                'subtotal' => $subtotal,
                'tax' => 0,
                'total_amount' => $subtotal,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? 'Manual procurement order generated.',
                'created_by' => auth()->id(),
            ]);

            foreach (array_slice($validated['items'], 1) as $item) {
                $product = $products->get($item['product_id']);
                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => (int) $item['quantity'],
                    'unit_price' => $product->unit_price,
                ]);
            }

            return $order;
        });

        // Cross-role notification: Staff -> Admin, Admin -> Staff
        $this->notifyPurchaseOrderParties($order, 'created');

        return redirect()->route('purchase-orders.show', $order)
            ->with('success', "Purchase order [{$order->reference}] generated successfully.");
    }

    /**
     * Show purchase order details.
     */
    public function show(PurchaseOrder $purchaseOrder): View
    {
        $this->authorize('view', $purchaseOrder);
        $purchaseOrder->load(['product.category', 'product.brand', 'items.product.category', 'items.product.brand', 'supplier', 'creator']);

        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    /**
     * Receive purchase order and increment stock.
     */
    public function receive(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->authorize('receive', $purchaseOrder);

        DB::transaction(function () use ($purchaseOrder): void {
            $order = PurchaseOrder::query()->lockForUpdate()->findOrFail($purchaseOrder->id);
            abort_unless(in_array($order->status, ['pending', 'approved', 'sent']), 409, 'Only pending or approved purchase orders can be received.');

            foreach ($order->items as $item) {
                if (!$item->product_id) {
                    continue;
                }

                Product::query()->lockForUpdate()->findOrFail($item->product_id);

                InventoryTransaction::create([
                    'product_id' => $item->product_id,
                    'user_id' => auth()->id(),
                    'purchase_order_id' => $order->id,
                    'type' => 'inbound',
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'reference' => $order->reference,
                    'reference_number' => $order->reference,
                    'notes' => 'Received against purchase order.',
                ]);
            }

            $order->update(['status' => 'received', 'received_at' => now()]);
        });

        $this->notifyPurchaseOrderParties($purchaseOrder, 'received');

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order received and stock automatically added to inventory.');
    }

    /**
     * Approve a pending purchase order.
     */
    public function approve(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->authorize('approve', $purchaseOrder);
        abort_unless(in_array($purchaseOrder->status, ['draft', 'pending']), 409, 'Only pending orders can be approved.');

        $purchaseOrder->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
        ]);

        $this->notifyPurchaseOrderParties($purchaseOrder, 'approved');

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', "Purchase order [{$purchaseOrder->reference}] approved successfully.");
    }

    /**
     * Reject a pending purchase order.
     */
    public function reject(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Only administrators can reject purchase orders.');
        abort_unless(in_array($purchaseOrder->status, ['draft', 'pending']), 409, 'Only pending orders can be rejected.');

        $reason = $request->input('rejection_reason') ?? $request->input('notes');

        $purchaseOrder->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        $this->notifyPurchaseOrderParties($purchaseOrder, 'rejected', $reason);

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('info', "Purchase order [{$purchaseOrder->reference}] has been marked as rejected.");
    }

    /**
     * Mark order as sent to supplier.
     */
    public function send(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Only administrators can mark purchase orders as sent.');
        abort_unless(in_array($purchaseOrder->status, ['pending', 'approved']), 409, 'Order must be pending or approved before sending.');

        $purchaseOrder->update(['status' => 'sent']);

        $this->notifyPurchaseOrderParties($purchaseOrder, 'sent');

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', "Purchase order [{$purchaseOrder->reference}] has been marked as sent to supplier.");
    }

    /**
     * Cancel a pending purchase order.
     */
    public function cancel(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user->isAdmin() || ($purchaseOrder->created_by === $user->id && $purchaseOrder->status === 'pending'), 403, 'Only administrators or the order creator can cancel purchase orders.');
        abort_unless($purchaseOrder->status === 'pending', 409, 'Only pending orders can be cancelled.');

        $reason = $request->input('notes') ?? $request->input('reason');
        $purchaseOrder->update(['status' => 'cancelled']);

        $this->notifyPurchaseOrderParties($purchaseOrder, 'cancelled', $reason);

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('info', "Purchase order [{$purchaseOrder->reference}] has been marked as cancelled.");
    }

    /**
     * Dedicated printable purchase order invoice / sheet.
     */
    public function printOrder(PurchaseOrder $purchaseOrder): View
    {
        $this->authorize('view', $purchaseOrder);
        $purchaseOrder->load(['product.category', 'product.brand', 'items.product.category', 'items.product.brand', 'supplier', 'creator']);

        return view('purchase-orders.print', compact('purchaseOrder'));
    }

    /**
     * Notify opposite role of any action on a purchase order.
     * Staff action -> notifies all Admins.
     * Admin action -> notifies order creator and all Staff.
     */
    protected function notifyPurchaseOrderParties(PurchaseOrder $order, string $action, ?string $reason = null): void
    {
        try {
            $actor = auth()->user() ?? $order->creator ?? User::first();
            if (!$actor) {
                return;
            }

            $order->loadMissing(['product', 'creator']);

            if ($actor->role === 'staff') {
                $recipients = User::where('role', 'admin')->get();
            } else {
                $recipients = User::where('role', 'staff')->get();
                if ($order->creator && $order->creator->role === 'staff' && !$recipients->contains('id', $order->creator->id)) {
                    $recipients->push($order->creator);
                }
            }

            foreach ($recipients as $recipient) {
                if ($recipient->id === $actor->id) {
                    continue;
                }

                // 1. In-app database notification (navbar bell dropdown)
                try {
                    $recipient->notify(new PurchaseOrderNotification($order, $action, $actor, $reason));
                } catch (\Throwable $notifError) {
                    Log::warning("Purchase order DB notification failed: {$notifError->getMessage()}");
                }

                // 2. Transactional email notification
                if (!empty($recipient->email)) {
                    try {
                        Mail::to($recipient->email, $recipient->name)
                            ->send(new PurchaseOrderNotification($order, $action, $actor, $reason));
                    } catch (\Throwable $mailError) {
                        Log::warning("Purchase order email notification failed to {$recipient->email}: {$mailError->getMessage()}");
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Purchase order notification trigger failed: {$e->getMessage()}");
        }
    }
}
