<?php

namespace App\Models;

use App\Observers\InventoryTransactionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([InventoryTransactionObserver::class])]
class InventoryTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'transactions';

    protected $fillable = [
        'product_id',
        'user_id',
        'purchase_order_id',
        'type',
        'quantity',
        'unit_price',
        'reference_number',
        'reference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Query scope: filter for stock in transactions.
     */
    public function scopeStockIn(Builder $query): Builder
    {
        return $query->whereIn('type', ['stock_in', 'in', 'inbound', 'purchase']);
    }

    /**
     * Query scope: filter for stock out transactions.
     */
    public function scopeStockOut(Builder $query): Builder
    {
        return $query->whereIn('type', ['stock_out', 'out', 'outbound', 'sale']);
    }

    /**
     * Query scope: filter for adjustment transactions.
     */
    public function scopeAdjustment(Builder $query): Builder
    {
        return $query->where('type', 'adjustment');
    }

    /**
     * Query scope: order by newest first.
     */
    public function scopeRecent(Builder $query): Builder
    {
        return $query->latest();
    }

    /**
     * Accessor for reference number with fallback to reference.
     */
    public function getReferenceNumberAttribute(): ?string
    {
        return $this->attributes['reference_number'] ?? $this->attributes['reference'] ?? null;
    }

    /**
     * Accessor for formatted type string.
     */
    public function getTypeDisplayAttribute(): string
    {
        return match (strtolower($this->type)) {
            'stock_in', 'in', 'inbound', 'purchase' => 'Stock In',
            'stock_out', 'out', 'outbound', 'sale' => 'Stock Out',
            'adjustment' => 'Adjustment',
            default => ucfirst($this->type),
        };
    }

    /**
     * Accessor for badge color class.
     */
    public function getTypeBadgeColorAttribute(): string
    {
        return match (strtolower($this->type)) {
            'stock_in', 'in', 'inbound', 'purchase' => 'success',
            'stock_out', 'out', 'outbound', 'sale' => 'danger',
            'adjustment' => 'warning',
            default => 'secondary',
        };
    }
}
