<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    private const EXPORTS = [
        'products' => [Product::class, ['id', 'name', 'sku', 'brand_id', 'category_id', 'supplier_id', 'quantity', 'reorder_level', 'unit_price', 'description', 'deleted_at', 'created_at', 'updated_at']],
        'brands' => [Brand::class, ['id', 'name', 'description', 'deleted_at', 'created_at', 'updated_at']],
        'categories' => [Category::class, ['id', 'name', 'description', 'deleted_at', 'created_at', 'updated_at']],
        'suppliers' => [Supplier::class, ['id', 'name', 'contact_person', 'email', 'phone', 'address', 'lead_time_days', 'deleted_at', 'created_at', 'updated_at']],
        'transactions' => [InventoryTransaction::class, ['id', 'product_id', 'user_id', 'purchase_order_id', 'type', 'quantity', 'unit_price', 'reference', 'notes', 'deleted_at', 'created_at', 'updated_at']],
        'purchase-orders' => [PurchaseOrder::class, ['id', 'reference', 'product_id', 'supplier_id', 'quantity', 'status', 'notes', 'created_by', 'received_at', 'deleted_at', 'created_at', 'updated_at']],
        'users' => [User::class, ['id', 'name', 'email', 'email_verified_at', 'role', 'deleted_at', 'created_at', 'updated_at']],
    ];

    // Generic CSV export for core tables
    public function download(string $table): Response|StreamedResponse
    {
        abort_unless(isset(self::EXPORTS[$table]), 404);
        // Authorization for sensitive data
        if ($table === 'users') {
            $this->authorize('viewAny', User::class);
        }

        [$model, $columns] = self::EXPORTS[$table];
        $filename = $table.'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($model, $columns) {
            $output = fopen('php://output', 'w');
            fputcsv($output, $columns, ',', "\"");
            $model::withTrashed()->orderBy('id')->chunk(500, function ($records) use ($output, $columns) {
                foreach ($records as $record) {
                    $row = [];
                    foreach ($columns as $column) {
                        $value = $record->getAttribute($column);
                        $value = is_scalar($value) ? (string) $value : '';
                        if ($value !== '' && preg_match('/^[=+\-@\t\r]/', $value)) {
                            $value = "'".$value;
                        }
                        $row[] = $value;
                    }
                    fputcsv($output, $row, ',', "\"");
                }
            });
            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ---------- PDF Exports ----------
    public function exportProductsPdf()
    {
        return (new \App\Exports\ProductsPdfExport)->download();
    }

    public function exportPurchaseOrdersPdf()
    {
        // Using a simple view – you can create a dedicated view similar to exports.products
        $orders = \App\Models\PurchaseOrder::all();
        $pdf = \PDF::loadView('exports.purchase_orders', compact('orders'));
        return $pdf->download('purchase_orders.pdf');
    }

    public function exportTransactionsPdf()
    {
        $transactions = \App\Models\InventoryTransaction::all();
        $pdf = \PDF::loadView('exports.transactions', compact('transactions'));
        return $pdf->download('transactions.pdf');
    }

    public function exportSuppliersPdf()
    {
        $suppliers = \App\Models\Supplier::all();
        $pdf = \PDF::loadView('exports.suppliers', compact('suppliers'));
        return $pdf->download('suppliers.pdf');
    }
}
