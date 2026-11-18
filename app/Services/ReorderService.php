<?php

namespace App\Services;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReorderService
{
    /**
     * Calculate average daily consumption based on past 30 days of stock out movements.
     */
    public function calculateAverageDailyUsage(Product $product): float
    {
        $totalOut = (float) $product->transactions()
            ->whereIn('type', ['stock_out', 'out', 'outbound', 'sale'])
            ->where('created_at', '>=', now()->subDays(30))
            ->sum('quantity');

        return $totalOut > 0 ? round($totalOut / 30, 2) : 0.5;
    }

    /**
     * Calculate dynamically recommended reorder point:
     * Reorder Point = (Average Daily Usage * Lead Time) + Safety Stock
     */
    public function calculateReorderPoint(Product $product): int
    {
        $averageDailyUsage = $this->calculateAverageDailyUsage($product);
        $leadTime = (int) ($product->supplier?->lead_time_days ?? 7);
        $safetyStock = (int) ($product->reorder_level ?? 10);

        return (int) ceil(($averageDailyUsage * $leadTime) + $safetyStock);
    }

    /**
     * Calculate optimal order replenishment quantity.
     */
    public function calculateOptimalOrderQuantity(Product $product): int
    {
        $deficit = max(0, $product->reorder_level - $product->quantity);
        $buffer = max(10, (int) ($product->reorder_level * 1.5));

        return max($deficit + $buffer, 10);
    }

    /**
     * Get all active products currently at or below their reorder threshold.
     */
    public function getProductsNeedingReorder(): Collection
    {
        return Product::with('supplier')
            ->whereColumn('quantity', '<=', 'reorder_level')
            ->get();
    }

    /**
     * Group products needing reorder by supplier ID.
     */
    public function groupProductsBySupplier($products): array
    {
        $grouped = [];
        foreach ($products as $product) {
            $supplierId = $product->supplier_id ?? 'unassigned';
            $grouped[$supplierId][] = $product;
        }

        return $grouped;
    }

    /**
     * Generate draft or pending purchase orders for all products needing replenishment.
     */
    public function generatePurchaseOrders(): array
    {
        $products = $this->getProductsNeedingReorder();
        $orders = [];

        foreach ($products as $product) {
            $order = $this->checkReorderPoint($product);
            if ($order && $order->wasRecentlyCreated) {
                $orders[] = $order;
            }
        }

        return $orders;
    }

    /**
     * Check single product reorder point and create pending PO if needed.
     */
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
                'created_by' => Auth::id() ?? User::value('id'),
            ]);
        });
    }

    /**
     * Check all products and create pending POs where inventory is depleted.
     */
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
