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
        'payment_terms',
        'lead_time_days',
        'minimum_order_amount',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'lead_time_days' => 'integer',
            'minimum_order_amount' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Scope: filter for active suppliers only.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope: filter by maximum lead time.
     */
    public function scopeWithLeadTime($query, int $days)
    {
        return $query->where('lead_time_days', '<=', $days);
    }

    /**
     * Scope: filter suppliers by name, contact, email, or active status.
     */
    public function scopeFilter($query, array $filters)
    {
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (isset($filters['active']) && $filters['active'] !== '') {
            $query->where('active', filter_var($filters['active'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query;
    }

    /**
     * Helper: returns formatted contact information.
     */
    public function getFullContactAttribute(): string
    {
        $parts = [];
        if ($this->contact_person) {
            $parts[] = $this->contact_person;
        }
        if ($this->email) {
            $parts[] = $this->email;
        }
        if ($this->phone) {
            $parts[] = $this->phone;
        }

        return !empty($parts) ? implode(' | ', $parts) : 'No contact details';
    }

    /**
     * Helper: calculates on-time delivery / fulfillment percentage.
     */
    public function getPerformanceRatingAttribute(): float
    {
        return $this->calculatePerformance();
    }

    /**
     * Total orders count.
     */
    public function getTotalOrdersCountAttribute(): int
    {
        return $this->purchaseOrders()->count();
    }

    /**
     * Total value of orders in PHP.
     */
    public function getTotalOrdersValueAttribute(): float
    {
        return (float) $this->purchaseOrders()
            ->join('products', 'purchase_orders.product_id', '=', 'products.id')
            ->selectRaw('COALESCE(SUM(purchase_orders.quantity * products.unit_price), 0) as total')
            ->value('total');
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
