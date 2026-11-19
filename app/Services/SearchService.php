<?php

namespace App\Services;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SearchService
{
    /**
     * Search products with multi-column filtering.
     */
    public function searchProducts(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return Product::with(['brand', 'category', 'supplier'])
            ->filter($filters)
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Search purchase orders with status, vendor, and date range filters.
     */
    public function searchPurchaseOrders(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return PurchaseOrder::with(['product', 'items.product', 'supplier', 'creator'])
            ->filter($filters)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Search inventory transactions / audit records.
     */
    public function searchTransactions(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = InventoryTransaction::with(['product', 'user']);

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (!empty($filters['type'])) {
            $type = $filters['type'];
            if ($type === 'stock_in') {
                $query->whereIn('type', ['stock_in', 'in', 'inbound', 'purchase']);
            } elseif ($type === 'stock_out') {
                $query->whereIn('type', ['stock_out', 'out', 'outbound', 'sale']);
            } elseif ($type === 'adjustment') {
                $query->where('type', 'adjustment');
            } else {
                $query->where('type', $type);
            }
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
            });
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Search suppliers with active status and keyword filtering.
     */
    public function searchSuppliers(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return Supplier::withCount('purchaseOrders')
            ->filter($filters)
            ->orderBy('name')
            ->paginate($perPage);
    }
}
