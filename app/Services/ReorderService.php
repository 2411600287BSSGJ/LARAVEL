<?php

namespace App\Services;

use App\Models\Product;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // <-- ADDED THIS

class ReorderService
{
    public function checkReorderPoint(Product $product): ?PurchaseOrder
    {
        return DB::transaction(function () use ($product): ?PurchaseOrder {
            $lockedProduct = Product::query()->lockForUpdate()->findOrFail($product->getKey());

            if ($lockedProduct->quantity >= $lockedProduct->reorder_level) {
                return null;
            }

            $pendingOrder = $lockedProduct->purchaseOrders()
                ->where('status', 'pending')
                ->latest('id')
                ->first();

            if ($pendingOrder !== null) {
                return $pendingOrder;
            }

            return PurchaseOrder::create([
                'reference' => PurchaseOrder::generateReference(),
                'product_id' => $lockedProduct->getKey(),
                'supplier_id' => $lockedProduct->getAttribute('supplier_id'),
                'quantity' => $lockedProduct->reorder_level - $lockedProduct->quantity,
                'status' => 'pending',
                'notes' => 'Automatically generated because stock is below the reorder level.',
                'created_by' => Auth::id() ?? \App\Models\User::value('id'),
            ]);
        });
    }

    public function checkAllReorderPoints(): int
    {
        $created = 0;

        $products = Product::query()
            ->whereColumn('quantity', '<', 'reorder_level')
            ->get();

        foreach ($products as $product) {
            $purchaseOrder = $this->checkReorderPoint($product);

            if ($purchaseOrder?->wasRecentlyCreated) {
                $created++;
            }
        }

        return $created;
    }
}