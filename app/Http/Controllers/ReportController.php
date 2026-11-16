<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Transaction;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('reports.index', [
            'lowStock' => Product::with(['brand', 'category', 'supplier'])
                ->whereColumn('quantity', '<=', 'reorder_level')
                ->where('quantity', '>', 0)
                ->orderBy('quantity')
                ->get(),
            'outOfStock' => Product::with(['brand', 'category'])->where('quantity', 0)->orderBy('name')->get(),
            'recentTransactions' => Transaction::with(['product', 'user'])->latest()->take(10)->get(),
            'pendingOrders' => PurchaseOrder::with(['product', 'supplier'])->where('status', 'pending')->latest()->get(),
            'supplierPerformance' => Supplier::withCount('purchaseOrders')->orderBy('name')->get(),
        ]);
    }
}
