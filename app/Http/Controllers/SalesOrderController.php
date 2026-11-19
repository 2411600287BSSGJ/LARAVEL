<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\User;
use App\Notifications\SalesOrderNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class SalesOrderController extends Controller
{
    /**
     * Display a listing of sales orders. Accessible by both admin and staff.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', SalesOrder::class);

        $query = SalesOrder::with(['product.category', 'product.brand', 'creator', 'approvedBy'])
            ->filter($request->only(['status', 'product_id', 'start_date', 'end_date', 'search']));

        $salesOrders = $query->latest()->paginate(15)->withQueryString();
        $products = Product::orderBy('name')->get();

        $stats = [
            'total' => SalesOrder::count(),
            'pending' => SalesOrder::where('status', 'pending')->count(),
            'approved' => SalesOrder::where('status', 'approved')->count(),
            'rejected' => SalesOrder::where('status', 'rejected')->count(),
            'revenue' => (float) SalesOrder::where('status', 'approved')->sum('total_amount'),
        ];

        return view('sales-orders.index', compact('salesOrders', 'products', 'stats'));
    }

    /**
     * Show the form for creating a new sales order. Accessible by both admin and staff.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', SalesOrder::class);

        $products = Product::with(['category', 'brand'])
            ->orderBy('name')
            ->get();

        $selectedProductId = $request->input('product_id');

        return view('sales-orders.create', compact('products', 'selectedProductId'));
    }

    /**
     * Store a newly created sales order.
     * Starts in 'pending' status — must be approved by an administrator.
     * Prevents out-of-stock products from being processed.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', SalesOrder::class);

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'customer_name.required' => 'Please provide customer or member name.',
            'product_id.required' => 'Please select an inventory product for this sale.',
            'quantity.required' => 'Please specify the quantity ordered.',
            'quantity.min' => 'Ordered quantity must be at least 1.',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        // Rule: If the product is out of stock it shouldn't be processed in sales order
        if ($product->quantity <= 0) {
            return back()->withErrors([
                'product_id' => "Cannot process sales order: Product '{$product->name}' is currently OUT OF STOCK (0 available)."
            ])->withInput();
        }

        if ($product->quantity < (int) $validated['quantity']) {
            return back()->withErrors([
                'quantity' => "Cannot order {$validated['quantity']} units. Only {$product->quantity} unit(s) currently available in stock for '{$product->name}'."
            ])->withInput();
        }

        $unitPrice = isset($validated['unit_price']) && $validated['unit_price'] !== ''
            ? (float) $validated['unit_price']
            : (float) $product->unit_price;

        $totalAmount = round((int) $validated['quantity'] * $unitPrice, 2);

        // Sales orders must be approved by admin regardless of creator
        $order = SalesOrder::create([
            'order_number' => SalesOrder::generateOrderNumber(),
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'] ?? null,
            'customer_phone' => $validated['customer_phone'] ?? null,
            'product_id' => $product->id,
            'quantity' => (int) $validated['quantity'],
            'unit_price' => $unitPrice,
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        // Cross-role notification: Staff -> Admin, Admin -> Staff
        $this->notifySalesOrderParties($order, 'created');

        return redirect()->route('sales-orders.show', $order)
            ->with('success', "Sales order [{$order->order_number}] submitted successfully. Awaiting administrator approval.");
    }

    /**
     * Display the specified sales order. Accessible by both admin and staff.
     */
    public function show(SalesOrder $salesOrder): View
    {
        $this->authorize('view', $salesOrder);
        $salesOrder->load(['product.category', 'product.brand', 'creator', 'approvedBy']);

        return view('sales-orders.show', compact('salesOrder'));
    }

    /**
     * Approve a pending sales order (Admin only).
     * Deducts stock and records an outbound inventory transaction.
     * Prevents processing if stock has become depleted in the meantime.
     */
    public function approve(SalesOrder $salesOrder): RedirectResponse
    {
        $this->authorize('approve', $salesOrder);

        abort_unless(auth()->user()->isAdmin(), 403, 'Only administrators can approve sales orders.');
        abort_unless($salesOrder->status === 'pending', 409, 'Only pending sales orders can be approved.');

        try {
            DB::transaction(function () use ($salesOrder): void {
                $order = SalesOrder::query()->lockForUpdate()->findOrFail($salesOrder->id);
                abort_unless($order->status === 'pending', 409, 'Only pending sales orders can be approved.');

                $product = Product::query()->lockForUpdate()->findOrFail($order->product_id);

                // Ensure product is in stock at the moment of approval
                if ($product->quantity <= 0) {
                    throw new \DomainException("Cannot approve sales order [{$order->order_number}]: Product '{$product->name}' is OUT OF STOCK (0 units available).");
                }

                if ($product->quantity < $order->quantity) {
                    throw new \DomainException("Cannot approve sales order [{$order->order_number}]: Insufficient stock for '{$product->name}'. Available: {$product->quantity}, Requested: {$order->quantity}.");
                }

                // Record outbound inventory transaction (Observer automatically decrements product quantity)
                InventoryTransaction::create([
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'type' => 'stock_out',
                    'quantity' => $order->quantity,
                    'unit_price' => $order->unit_price,
                    'reference_number' => $order->order_number,
                    'reference' => $order->order_number,
                    'notes' => "Fulfilled against Sales Order {$order->order_number} for customer {$order->customer_name}",
                ]);

                $order->update([
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);
            });
        } catch (\DomainException $de) {
            return back()->with('error', $de->getMessage());
        }

        $this->notifySalesOrderParties($salesOrder->fresh(), 'approved');

        return redirect()->route('sales-orders.show', $salesOrder)
            ->with('success', "Sales order [{$salesOrder->order_number}] approved. {$salesOrder->quantity} unit(s) deducted from inventory.");
    }

    /**
     * Reject a pending sales order (Admin only).
     */
    public function reject(Request $request, SalesOrder $salesOrder): RedirectResponse
    {
        $this->authorize('reject', $salesOrder);

        abort_unless(auth()->user()->isAdmin(), 403, 'Only administrators can reject sales orders.');
        abort_unless($salesOrder->status === 'pending', 409, 'Only pending sales orders can be rejected.');

        $reason = $request->input('rejection_reason') ?? $request->input('notes') ?? 'Declined by administrator';

        $salesOrder->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        $this->notifySalesOrderParties($salesOrder, 'rejected', $reason);

        return redirect()->route('sales-orders.show', $salesOrder)
            ->with('info', "Sales order [{$salesOrder->order_number}] has been marked as rejected.");
    }

    /**
     * Cancel a pending sales order (Admin or Order Creator).
     */
    public function cancel(Request $request, SalesOrder $salesOrder): RedirectResponse
    {
        $this->authorize('cancel', $salesOrder);

        $user = auth()->user();
        abort_unless($user->isAdmin() || ($salesOrder->created_by === $user->id && $salesOrder->status === 'pending'), 403, 'Unauthorized to cancel this sales order.');
        abort_unless($salesOrder->status === 'pending', 409, 'Only pending sales orders can be cancelled.');

        $reason = $request->input('notes') ?? 'Cancelled by user';
        $salesOrder->update(['status' => 'cancelled']);

        $this->notifySalesOrderParties($salesOrder, 'cancelled', $reason);

        return redirect()->route('sales-orders.show', $salesOrder)
            ->with('info', "Sales order [{$salesOrder->order_number}] has been marked as cancelled.");
    }

    /**
     * Print clean sales order invoice / receipt.
     */
    public function printOrder(SalesOrder $salesOrder): View
    {
        $this->authorize('view', $salesOrder);
        $salesOrder->load(['product.category', 'product.brand', 'creator', 'approvedBy']);

        return view('sales-orders.print', compact('salesOrder'));
    }

    /**
     * Cross-role notification helper for sales order events.
     * Staff action -> notifies all Admins.
     * Admin action -> notifies order creator and all Staff.
     */
    protected function notifySalesOrderParties(SalesOrder $order, string $action, ?string $reason = null): void
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

                // 1. In-app database notification (shows on navbar bell badge)
                try {
                    $recipient->notify(new SalesOrderNotification($order, $action, $actor, $reason));
                } catch (\Throwable $notifError) {
                    Log::warning("Sales order DB notification failed: {$notifError->getMessage()}");
                }

                // 2. Transactional email notification
                if (!empty($recipient->email)) {
                    try {
                        Mail::to($recipient->email, $recipient->name)
                            ->send(new SalesOrderNotification($order, $action, $actor, $reason));
                    } catch (\Throwable $mailError) {
                        Log::warning("Sales order email notification failed to {$recipient->email}: {$mailError->getMessage()}");
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Sales order notification trigger failed: {$e->getMessage()}");
        }
    }
}
