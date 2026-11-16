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

    // Products (CRUD Resource with show)
    Route::resource('products', ProductController::class);

    // Brands, Categories, Suppliers
    Route::resource('brands', BrandController::class)->except(['show']);
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('suppliers', SupplierController::class)->except(['show']);

    // Inventory Transactions
    Route::get('/transactions', [InventoryTransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/create', [InventoryTransactionController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [InventoryTransactionController::class, 'store'])->name('transactions.store');
    Route::get('/transactions/{transaction}', [InventoryTransactionController::class, 'show'])->name('transactions.show');
    Route::delete('/transactions/{transaction}', [InventoryTransactionController::class, 'destroy'])->name('transactions.destroy');

    // Stock Adjustments
    Route::get('/adjustments/create', [InventoryTransactionController::class, 'createAdjustment'])->name('adjustments.create');
    Route::post('/adjustments', [InventoryTransactionController::class, 'storeAdjustment'])->name('adjustments.store');

    // Purchase Orders
    Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
    Route::get('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
    Route::post('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');

    // Reports and CSV Exports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/stock-movement', [ReportController::class, 'exportStockMovement'])->name('reports.export.stock-movement');
    Route::get('/reports/export/low-stock', [ReportController::class, 'exportLowStock'])->name('reports.export.low-stock');
    Route::get('/reports/export/valuation', [ReportController::class, 'exportValuation'])->name('reports.export.valuation');
    Route::get('/exports/{table}', [ExportController::class, 'download'])->name('exports.download');

    // Notifications
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllRead');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::get('/notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread');

    // API endpoints for real-time dashboard updates & dynamic UI
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/dashboard/stats', [DashboardApiController::class, 'stats'])->name('dashboard.stats');
        Route::get('/products/low-stock', [DashboardApiController::class, 'lowStock'])->name('products.low-stock');
        Route::get('/transactions/recent', [DashboardApiController::class, 'recentTransactions'])->name('transactions.recent');
    });

    // Users (admin)
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.role');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});

require __DIR__.'/auth.php';
