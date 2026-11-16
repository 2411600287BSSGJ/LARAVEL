<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\PurchaseOrder;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard', [
            'totalProducts' => Product::count(),
            'lowStock' => Product::whereColumn('quantity', '<=', 'reorder_level')
                ->where('quantity', '>', 0)
                ->count(),
            'outOfStock' => Product::where('quantity', 0)->count(),
            'inventoryValue' => Product::query()->selectRaw('COALESCE(SUM(quantity * unit_price), 0) as value')->value('value'),
            'recentProducts' => Product::with(['brand', 'category'])->latest()->take(8)->get(),
            'pendingOrders' => PurchaseOrder::with(['product', 'supplier'])->where('status', 'pending')->latest()->take(5)->get(),
            'recentTransactions' => InventoryTransaction::with(['product', 'user'])->latest()->take(8)->get(),
        ]);
    }
}
