<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'sku', 'brand_id', 'category_id', 'supplier_id',
        'quantity', 'reorder_level', 'unit_price', 'description',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'reorder_level' => 'integer',
            'unit_price' => 'decimal:2',
        ];
    }

    public function brand(): BelongsTo { return $this->belongsTo(Brand::class); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function transactions(): HasMany { return $this->hasMany(Transaction::class); }
    public function purchaseOrders(): HasMany { return $this->hasMany(PurchaseOrder::class); }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($filters['brand_id'] ?? null, fn (Builder $query, $id): Builder => $query->where('brand_id', $id))
            ->when($filters['category_id'] ?? null, fn (Builder $query, $id): Builder => $query->where('category_id', $id))
            ->when($filters['stock'] ?? null, function (Builder $query, string $stock): void {
                if ($stock === 'low') {
                    $query->whereColumn('quantity', '<=', 'reorder_level')->where('quantity', '>', 0);
                } elseif ($stock === 'out') {
                    $query->where('quantity', 0);
                } elseif ($stock === 'in_stock') {
                    $query->where('quantity', '>', 0);
                }
            });
    }
}