<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard', $this->pageData());
    }

    public function stats(): JsonResponse
    {
        return response()->json($this->statsData());
    }

    private function pageData(): array
    {
        return $this->statsData() + [
            'recentProducts' => Product::with(['brand', 'category'])->latest()->take(8)->get(),
            'pendingOrders' => PurchaseOrder::with(['product', 'supplier'])
                ->where('status', 'pending')
                ->latest()
                ->take(5)
                ->get(),
            'recentTransactions' => Transaction::with(['product', 'user'])->latest()->take(8)->get(),
        ];
    }

    private function statsData(): array
    {
        $categoryStocks = Category::query()
            ->withSum('products', 'quantity')
            ->orderBy('name')
            ->get();

        $days = collect(range(6, 0))
            ->map(fn (int $daysAgo) => now()->subDays($daysAgo)->startOfDay());
        $transactionsByDay = Transaction::query()
            ->where('created_at', '>=', $days->first())
            ->get(['created_at', 'type', 'quantity'])
            ->groupBy(fn (Transaction $transaction) => $transaction->created_at->format('Y-m-d'));

        $dailyInbound = $this->dailyQuantities($days, $transactionsByDay, 'in');
        $dailyOutbound = $this->dailyQuantities($days, $transactionsByDay, 'out');

        return [
            'totalProducts' => Product::count(),
            'lowStock' => Product::whereColumn('quantity', '<=', 'reorder_level')
                ->where('quantity', '>', 0)
                ->count(),
            'outOfStock' => Product::where('quantity', 0)->count(),
            'inventoryValue' => (float) Product::query()
                ->selectRaw('COALESCE(SUM(quantity * unit_price), 0) as value')
                ->value('value'),
            'chartData' => [
                'categoryLabels' => $categoryStocks->pluck('name'),
                'categoryStock' => $categoryStocks->map(fn (Category $category) => (int) ($category->products_sum_quantity ?? 0)),
                'days' => $days->map(fn ($day) => $day->format('D')),
                'inbound' => $dailyInbound,
                'outbound' => $dailyOutbound,
            ],
        ];
    }

    private function dailyQuantities(Collection $days, Collection $transactionsByDay, string $type): Collection
    {
        return $days->map(function ($day) use ($transactionsByDay, $type): int {
            return (int) $transactionsByDay
                ->get($day->format('Y-m-d'), collect())
                ->where('type', $type)
                ->sum('quantity');
        });
    }
}
