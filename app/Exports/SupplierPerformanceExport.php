<?php
namespace App\Exports;

use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SupplierPerformanceExport implements FromQuery, WithHeadings
{
    public function query()
    {
        return Supplier::query()->select([
            'id',
            'name',
            'email',
            'phone',
            'address',
            // placeholder performance columns – in a real app you might calculate these via joins or scopes
            'total_orders',
            'on_time_delivery_rate',
            'average_lead_time_days',
        ]);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Email',
            'Phone',
            'Address',
            'Total Orders',
            'On‑Time Delivery Rate',
            'Avg Lead Time (days)',
        ];
    }
}
