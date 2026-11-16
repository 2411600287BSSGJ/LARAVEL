<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'lead_time_days',
    ];

    protected function casts(): array
    {
        return ['lead_time_days' => 'integer'];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function calculatePerformance(): float
    {
        $orders = $this->purchaseOrders();
        $total = (clone $orders)->count();

        if ($total === 0) {
            return 0.0;
        }

        $received = (clone $orders)->where('status', 'received')->count();

        return round(($received / $total) * 100, 2);
    }
}
