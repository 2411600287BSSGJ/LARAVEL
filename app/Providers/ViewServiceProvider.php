<?php

namespace App\Providers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any view services and composers.
     */
    public function boot(): void
    {
        // 1. Pending Purchase Orders Widget Composer
        View::composer('widgets.pending-orders', function ($view) {
            $pendingOrdersCount = PurchaseOrder::where('status', 'pending')->count();
            $pendingOrders = PurchaseOrder::with(['product', 'items.product', 'supplier'])
                ->where('status', 'pending')
                ->latest()
                ->take(5)
                ->get();

            $view->with([
                'pendingOrdersCount' => $pendingOrdersCount,
                'pendingOrders' => $pendingOrders,
            ]);
        });

        // 2. Recent Purchase Orders Widget Composer
        View::composer('widgets.recent-orders', function ($view) {
            $recentOrders = PurchaseOrder::with(['product', 'items.product', 'supplier'])
                ->latest()
                ->take(6)
                ->get();

            $view->with('recentOrders', $recentOrders);
        });

        // 3. Supplier Performance Widget Composer
        View::composer('widgets.supplier-performance', function ($view) {
            $suppliers = Supplier::withCount('purchaseOrders')
                ->orderBy('name')
                ->get()
                ->map(function ($supplier) {
                    $supplier->performance_score = $supplier->calculatePerformance();
                    return $supplier;
                })
                ->sortByDesc('performance_score')
                ->take(5);

            $view->with('topSuppliers', $suppliers);
        });

        // 4. Reorder Alerts Widget Composer
        View::composer('widgets.reorder-alerts', function ($view) {
            $reorderAlerts = Product::with(['category', 'supplier'])
                ->whereColumn('quantity', '<=', 'reorder_level')
                ->orderBy('quantity', 'asc')
                ->take(6)
                ->get();

            $view->with('reorderAlerts', $reorderAlerts);
        });
    }
}
