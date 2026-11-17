<?php

namespace App\Models;

use App\Observers\ProductObserver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([ProductObserver::class])]
class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'sku',
        'description',
        'category',
        'category_id',
        'brand_id',
        'supplier',
        'supplier_id',
        'quantity',
        'reorder_level',
        'unit_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'reorder_level' => 'integer',
            'unit_price' => 'decimal:2',
        ];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function latestTransaction(): HasOne
    {
        return $this->hasOne(InventoryTransaction::class)->latestOfMany();
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Business logic helper: check if product is low stock.
     */
    public function isLowStock(): bool
    {
        return $this->quantity <= $this->reorder_level && $this->quantity > 0;
    }

    /**
     * Business logic helper: check if product is out of stock.
     */
    public function isOutOfStock(): bool
    {
        return $this->quantity <= 0;
    }

    /**
     * Accessor for human-readable stock status.
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->isOutOfStock()) {
            return 'Out of Stock';
        }
        if ($this->quantity <= $this->reorder_level) {
            return 'Low Stock';
        }
        return 'In Stock';
    }

    /**
     * Accessor for Bootstrap status badge color class.
     */
    public function getStockStatusColorAttribute(): string
    {
        if ($this->isOutOfStock()) {
            return 'danger';
        }
        if ($this->quantity <= $this->reorder_level) {
            return 'warning';
        }
        return 'success';
    }

    /**
     * Accessor for category display name.
     */
    public function getCategoryNameAttribute(): string
    {
        if (!empty($this->attributes['category'])) {
            return $this->attributes['category'];
        }
        return $this->category?->name ?? 'Uncategorized';
    }

    /**
     * Accessor for supplier display name.
     */
    public function getSupplierNameAttribute(): string
    {
        if (!empty($this->attributes['supplier'])) {
            return $this->attributes['supplier'];
        }
        return $this->supplier?->name ?? 'N/A';
    }

    /**
     * Accessor for total valuation of this product in stock.
     */
    public function getTotalValueAttribute(): float
    {
        return (float) ($this->quantity * (float) $this->unit_price);
    }

    /**
     * Query scope: products at or below reorder level.
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('quantity', '<=', 'reorder_level')->where('quantity', '>', 0);
    }

    /**
     * Query scope: products with zero or negative quantity.
     */
    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->where('quantity', '<=', 0);
    }

    /**
     * Query scope: products in a specific category.
     */
    public function scopeInCategory(Builder $query, string $category): Builder
    {
        return $query->where(function (Builder $q) use ($category) {
            $q->where('category', $category)
                ->orWhereHas('category', fn (Builder $c) => $c->where('name', $category));
        });
    }

    /**
     * Query scope: filter by search term, category, brand, and stock status.
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhere('supplier', 'like', "%{$search}%");
                });
            })
            ->when($filters['category'] ?? null, function (Builder $query, string $cat): void {
                $query->where(function (Builder $q) use ($cat) {
                    $q->where('category', $cat)
                        ->orWhere('category_id', $cat);
                });
            })
            ->when($filters['brand_id'] ?? null, fn (Builder $query, $id): Builder => $query->where('brand_id', $id))
            ->when($filters['category_id'] ?? null, fn (Builder $query, $id): Builder => $query->where('category_id', $id))
            ->when($filters['stock'] ?? null, function (Builder $query, string $stock): void {
                if ($stock === 'low') {
                    $query->whereColumn('quantity', '<=', 'reorder_level')->where('quantity', '>', 0);
                } elseif ($stock === 'out') {
                    $query->where('quantity', '<=', 0);
                } elseif ($stock === 'in_stock') {
                    $query->whereColumn('quantity', '>', 'reorder_level');
                }
            });
    }
}
