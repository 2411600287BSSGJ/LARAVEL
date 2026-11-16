<?php
namespace App\Exports;

use App\Models\InventoryTransaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InventoryTransactionsExport implements FromQuery, WithHeadings
{
    public function query()
    {
        return InventoryTransaction::query()->select([
            'id',
            'product_id',
            'user_id',
            'type',
            'quantity',
            'reference',
            'notes',
            'created_at',
            'updated_at',
        ]);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Product ID',
            'User ID',
            'Type',
            'Quantity',
            'Reference',
            'Notes',
            'Created At',
            'Updated At',
        ];
    }
}
