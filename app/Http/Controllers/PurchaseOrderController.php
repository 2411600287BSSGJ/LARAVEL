<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    /**
     * Display listing of purchase orders with status, product, and supplier filters.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', PurchaseOrder::class);

        $query = PurchaseOrder::with(['product', 'supplier', 'creator']);

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
                  ->orWhereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
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

        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $supplierId = $validated['supplier_id'] ?? $product->supplier_id;

        $order = PurchaseOrder::create([
            'reference' => PurchaseOrder::generateReference(),
            'product_id' => $product->id,
            'supplier_id' => $supplierId,
            'quantity' => (int) $validated['quantity'],
            'status' => 'pending',
            'notes' => $validated['notes'] ?? 'Manual procurement order generated.',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('purchase-orders.show', $order)
            ->with('success', "Purchase order [{$order->reference}] generated successfully.");
    }

    /**
     * Show purchase order details.
     */
    public function show(PurchaseOrder $purchaseOrder): View
    {
        $this->authorize('view', $purchaseOrder);
        $purchaseOrder->load(['product.category', 'product.brand', 'supplier', 'creator']);

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
            abort_unless($order->status === 'pending', 409, 'Only pending purchase orders can be received.');

            $product = Product::query()->lockForUpdate()->findOrFail($order->product_id);

            InventoryTransaction::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'purchase_order_id' => $order->id,
                'type' => 'inbound',
                'quantity' => $order->quantity,
                'unit_price' => $product->unit_price,
                'reference' => $order->reference,
                'reference_number' => $order->reference,
                'notes' => 'Received against purchase order.',
            ]);

            $order->update(['status' => 'received', 'received_at' => now()]);
        });

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order received and stock automatically added to inventory.');
    }

    /**
     * Cancel a pending purchase order.
     */
    public function cancel(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Only administrators can cancel purchase orders.');
        abort_unless($purchaseOrder->status === 'pending', 409, 'Only pending orders can be cancelled.');

        $purchaseOrder->update(['status' => 'cancelled']);

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('info', "Purchase order [{$purchaseOrder->reference}] has been marked as cancelled.");
    }

    /**
     * Dedicated printable purchase order invoice / sheet.
     */
    public function printOrder(PurchaseOrder $purchaseOrder): View
    {
        $this->authorize('view', $purchaseOrder);
        $purchaseOrder->load(['product.category', 'product.brand', 'supplier', 'creator']);

        return view('purchase-orders.print', compact('purchaseOrder'));
    }
}
