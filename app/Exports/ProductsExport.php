<?php
namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductsExport implements FromQuery, WithHeadings
{
    public function query()
    {
        return Product::query()->select([
            'id',
            'name',
            'sku',
            'category',
            'quantity',
            'reorder_level',
            'unit_price',
            'supplier',
        ]);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'SKU',
            'Category',
            'Quantity',
            'Reorder Level',
            'Unit Price',
            'Supplier',
        ];
    }
}
