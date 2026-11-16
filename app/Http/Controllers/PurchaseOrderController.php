<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', PurchaseOrder::class);
        $purchaseOrders = PurchaseOrder::with(['product', 'supplier', 'creator'])
            ->latest()
            ->paginate(20);

        return view('purchase-orders.index', compact('purchaseOrders'));
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        $this->authorize('view', $purchaseOrder);
        $purchaseOrder->load(['product', 'supplier', 'creator']);

        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    public function receive(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->authorize('receive', $purchaseOrder);

        DB::transaction(function () use ($purchaseOrder): void {
            $order = PurchaseOrder::query()->lockForUpdate()->findOrFail($purchaseOrder->id);
            abort_unless($order->status === 'pending', 409, 'Only pending purchase orders can be received.');

            $product = Product::query()->lockForUpdate()->findOrFail($order->product_id);
            $product->increment('quantity', $order->quantity);

            Transaction::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'purchase_order_id' => $order->id,
                'type' => 'in',
                'quantity' => $order->quantity,
                'unit_price' => $product->unit_price,
                'reference' => $order->reference,
                'notes' => 'Received against purchase order.',
            ]);

            $order->update(['status' => 'received', 'received_at' => now()]);
        });

        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', 'Purchase order received.');
    }
}
