<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Display inventory reports overview and filtered tables.
     */
    public function index(Request $request): View
    {
        $startDate = $request->input('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        // Stock movements query within date range
        $movements = InventoryTransaction::with(['product', 'user'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->latest()
            ->paginate(15, ['*'], 'movements_page')
            ->withQueryString();

        // Low stock products query
        $lowStockProducts = Product::with(['category', 'supplier'])
            ->whereColumn('quantity', '<=', 'reorder_level')
            ->orderBy('quantity', 'asc')
            ->get();

        // Valuation report
        $valuationProducts = Product::with(['category', 'supplier'])
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $totalInventoryValue = $valuationProducts->sum('total_value');
        $totalItemsCount = $valuationProducts->sum('quantity');

        // Valuation grouped by category
        $valuationByCategory = $valuationProducts->groupBy(fn ($p) => $p->category_name)
            ->map(function ($items, $categoryName) {
                return [
                    'category' => $categoryName,
                    'count' => $items->count(),
                    'total_quantity' => $items->sum('quantity'),
                    'total_value' => $items->sum('total_value'),
                ];
            });

        return view('reports.index', [
            'movements' => $movements,
            'lowStockProducts' => $lowStockProducts,
            'valuationProducts' => $valuationProducts,
            'totalInventoryValue' => $totalInventoryValue,
            'totalItemsCount' => $totalItemsCount,
            'valuationByCategory' => $valuationByCategory,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    /**
     * Export Stock Movements report to CSV.
     */
    public function exportStockMovement(Request $request): StreamedResponse
    {
        $startDate = $request->input('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $fileName = 'stock_movements_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($startDate, $endDate) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Date', 'Product Name', 'SKU', 'Type', 'Quantity', 'Unit Price', 'Reference', 'Authorized By', 'Notes']);

            InventoryTransaction::with(['product', 'user'])
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->latest()
                ->chunk(100, function ($transactions) use ($handle) {
                    foreach ($transactions as $tx) {
                        fputcsv($handle, [
                            $tx->id,
                            $tx->created_at->format('Y-m-d H:i:s'),
                            $tx->product?->name ?? 'N/A',
                            $tx->product?->sku ?? 'N/A',
                            $tx->type_display,
                            $tx->quantity,
                            $tx->unit_price,
                            $tx->reference_number,
                            $tx->user?->name ?? 'System',
                            $tx->notes,
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    /**
     * Export Low Stock report to CSV.
     */
    public function exportLowStock(): StreamedResponse
    {
        $fileName = 'low_stock_report_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Product Name', 'SKU', 'Category', 'Current Quantity', 'Reorder Level', 'Unit Price', 'Supplier', 'Status']);

            Product::whereColumn('quantity', '<=', 'reorder_level')
                ->orderBy('quantity', 'asc')
                ->chunk(100, function ($products) use ($handle) {
                    foreach ($products as $p) {
                        fputcsv($handle, [
                            $p->id,
                            $p->name,
                            $p->sku,
                            $p->category_name,
                            $p->quantity,
                            $p->reorder_level,
                            $p->unit_price,
                            $p->supplier_name,
                            $p->stock_status,
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    /**
     * Export Valuation report to CSV.
     */
    public function exportValuation(): StreamedResponse
    {
        $fileName = 'inventory_valuation_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Product Name', 'SKU', 'Category', 'Quantity on Hand', 'Unit Price', 'Total Valuation', 'Supplier']);

            Product::orderBy('name')
                ->chunk(100, function ($products) use ($handle) {
                    foreach ($products as $p) {
                        fputcsv($handle, [
                            $p->id,
                            $p->name,
                            $p->sku,
                            $p->category_name,
                            $p->quantity,
                            $p->unit_price,
                            number_format($p->total_value, 2, '.', ''),
                            $p->supplier_name,
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}
