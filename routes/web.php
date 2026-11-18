<?php

use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\InventoryTransactionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Products (CRUD Resource with show and barcode tag)
    Route::get('/products/{product}/barcode', [ProductController::class, 'barcode'])->name('products.barcode');
    Route::resource('products', ProductController::class);

    // Brands, Categories, Suppliers
    Route::resource('brands', BrandController::class)->except(['show']);
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('suppliers', SupplierController::class);

    // Inventory Transactions / Audit Trail
    Route::get('/transactions', [InventoryTransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/create', [InventoryTransactionController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [InventoryTransactionController::class, 'store'])->name('transactions.store');
    Route::get('/transactions-export-csv', [InventoryTransactionController::class, 'exportCsv'])->name('transactions.export.csv');
    Route::get('/transactions-print', [InventoryTransactionController::class, 'printIndex'])->name('transactions.print');
    Route::get('/transactions/{transaction}', [InventoryTransactionController::class, 'show'])->name('transactions.show');
    Route::get('/transactions/{transaction}/edit', [InventoryTransactionController::class, 'edit'])->name('transactions.edit');
    Route::put('/transactions/{transaction}', [InventoryTransactionController::class, 'update'])->name('transactions.update');
    Route::delete('/transactions/{transaction}', [InventoryTransactionController::class, 'destroy'])->name('transactions.destroy');
    Route::get('/transactions/{transaction}/print', [InventoryTransactionController::class, 'printShow'])->name('transactions.print.show');

    // Audit Trail Route Aliases
    Route::get('/audit-trail', [InventoryTransactionController::class, 'index'])->name('audit-trail.index');
    Route::get('/audit-trail/create', [InventoryTransactionController::class, 'create'])->name('audit-trail.create');
    Route::get('/audit-trail/export-csv', [InventoryTransactionController::class, 'exportCsv'])->name('audit-trail.export.csv');
    Route::get('/audit-trail/print', [InventoryTransactionController::class, 'printIndex'])->name('audit-trail.print');
    Route::get('/audit-trail/{transaction}', [InventoryTransactionController::class, 'show'])->name('audit-trail.show');
    Route::get('/audit-trail/{transaction}/edit', [InventoryTransactionController::class, 'edit'])->name('audit-trail.edit');

    // Stock Adjustments
    Route::get('/adjustments/create', [InventoryTransactionController::class, 'createAdjustment'])->name('adjustments.create');
    Route::post('/adjustments', [InventoryTransactionController::class, 'storeAdjustment'])->name('adjustments.store');

    // Purchase Orders (Procurement)
    Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
    Route::get('/purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
    Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
    Route::get('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
    Route::post('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
    Route::post('/purchase-orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve');
    Route::post('/purchase-orders/{purchaseOrder}/reject', [PurchaseOrderController::class, 'reject'])->name('purchase-orders.reject');
    Route::post('/purchase-orders/{purchaseOrder}/send', [PurchaseOrderController::class, 'send'])->name('purchase-orders.send');
    Route::post('/purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');
    Route::get('/purchase-orders/{purchaseOrder}/print', [PurchaseOrderController::class, 'printOrder'])->name('purchase-orders.print');

    // Reports and CSV Exports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/stock-movement', [ReportController::class, 'exportStockMovement'])->name('reports.export.stock-movement');
    Route::get('/reports/export/low-stock', [ReportController::class, 'exportLowStock'])->name('reports.export.low-stock');
    Route::get('/reports/export/valuation', [ReportController::class, 'exportValuation'])->name('reports.export.valuation');
    Route::get('/reports/export/supplier-performance', [ReportController::class, 'exportSupplierPerformance'])->name('reports.export.supplier-performance');
    Route::get('/exports/{table}', [ExportController::class, 'download'])->name('exports.download');
    // CSV specific shortcuts
    Route::get('export/products/csv', [ExportController::class, 'download'])->where('table', 'products')->name('export.products.csv');
    Route::get('export/purchase-orders/csv', [ExportController::class, 'download'])->where('table', 'purchase-orders')->name('export.purchase_orders.csv');
    Route::get('export/transactions/csv', [ExportController::class, 'download'])->where('table', 'transactions')->name('export.transactions.csv');
    Route::get('export/suppliers/csv', [ExportController::class, 'download'])->where('table', 'suppliers')->name('export.suppliers.csv');

    // Notifications
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllRead');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::get('/notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread');

    // API endpoints for real-time dashboard updates & dynamic UI
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/dashboard/stats', [DashboardApiController::class, 'stats'])->name('dashboard.stats');
        Route::get('/products/low-stock', [DashboardApiController::class, 'lowStock'])->name('products.low-stock');
        Route::get('/transactions/recent', [DashboardApiController::class, 'recentTransactions'])->name('transactions.recent');
        Route::get('/dashboard/pending-orders', [DashboardApiController::class, 'pendingOrders'])->name('dashboard.pending-orders');
        Route::get('/dashboard/recent-orders', [DashboardApiController::class, 'recentOrders'])->name('dashboard.recent-orders');
        Route::get('/dashboard/supplier-performance', [DashboardApiController::class, 'supplierPerformance'])->name('dashboard.supplier-performance');
        Route::get('/dashboard/reorder-alerts', [DashboardApiController::class, 'reorderAlerts'])->name('dashboard.reorder-alerts');
    });

    // Users (admin)
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.role');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});

require __DIR__.'/auth.php';
