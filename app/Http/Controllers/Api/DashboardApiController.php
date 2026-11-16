<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class DashboardApiController extends Controller
{
    /**
     * GET /api/dashboard/stats
     * Return key statistics for real-time dashboard updates.
     */
    public function stats(): JsonResponse
    {
        $totalProducts = Product::count();
        $lowStockCount = Product::whereColumn('quantity', '<=', 'reorder_level')
            ->where('quantity', '>', 0)
            ->count();
        $outOfStockCount = Product::where('quantity', '<=', 0)->count();
        $inventoryValue = (float) Product::query()
            ->selectRaw('COALESCE(SUM(quantity * unit_price), 0) as total_val')
            ->value('total_val');

        $recentTransactionsCount = InventoryTransaction::where('created_at', '>=', now()->subDay())->count();

        return response()->json([
            'total_products' => $totalProducts,
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
            'total_inventory_value' => $inventoryValue,
            'formatted_inventory_value' => '₱' . number_format($inventoryValue, 2),
            'recent_transactions_count' => $recentTransactionsCount,
            'last_updated' => now()->format('M d, Y h:i:s A'),
        ]);
    }

    /**
     * GET /api/products/low-stock
     * Return list of products running low on stock.
     */
    public function lowStock(): JsonResponse
    {
        $products = Product::with(['category', 'supplier'])
            ->whereColumn('quantity', '<=', 'reorder_level')
            ->orderBy('quantity', 'asc')
            ->get()
            ->map(function (Product $p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'category' => $p->category_name,
                    'supplier' => $p->supplier_name,
                    'quantity' => $p->quantity,
                    'reorder_level' => $p->reorder_level,
                    'unit_price' => (float) $p->unit_price,
                    'formatted_price' => '₱' . number_format($p->unit_price, 2),
                    'status' => $p->stock_status,
                    'status_color' => $p->stock_status_color,
                    'show_url' => route('products.show', $p->id),
                ];
            });

        return response()->json($products);
    }

    /**
     * GET /api/transactions/recent
     * Return list of recent transactions.
     */
    public function recentTransactions(): JsonResponse
    {
        $transactions = InventoryTransaction::with(['product', 'user'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function (InventoryTransaction $tx) {
                return [
                    'id' => $tx->id,
                    'product_id' => $tx->product_id,
                    'product_name' => $tx->product?->name ?? 'Deleted Product',
                    'product_sku' => $tx->product?->sku ?? 'N/A',
                    'type' => $tx->type_display,
                    'raw_type' => $tx->type,
                    'type_badge' => $tx->type_badge_color,
                    'quantity' => $tx->quantity,
                    'reference_number' => $tx->reference_number ?? ('#' . $tx->id),
                    'user_name' => $tx->user?->name ?? 'System',
                    'created_at' => $tx->created_at->format('M d, Y H:i'),
                    'time_ago' => $tx->created_at->diffForHumans(),
                    'product_url' => $tx->product ? route('products.show', $tx->product_id) : '#',
                ];
            });

        return response()->json($transactions);
    }
}
