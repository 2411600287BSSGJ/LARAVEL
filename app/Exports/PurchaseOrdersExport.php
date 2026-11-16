<?php
namespace App\Exports;

use App\Models\PurchaseOrder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PurchaseOrdersExport implements FromQuery, WithHeadings
{
    public function query()
    {
        return PurchaseOrder::query()->select([
            'id',
            'reference',
            'product_id',
            'supplier_id',
            'quantity',
            'status',
            'notes',
            'created_by',
            'received_at',
            'created_at',
            'updated_at',
        ]);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Reference',
            'Product ID',
            'Supplier ID',
            'Quantity',
            'Status',
            'Notes',
            'Created By',
            'Received At',
            'Created At',
            'Updated At',
        ];
    }
}
