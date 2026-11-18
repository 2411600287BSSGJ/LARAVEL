<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference',
        'product_id',
        'supplier_id',
        'quantity',
        'status',
        'notes',
        'created_by',
        'approved_by',
        'received_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'received_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope: orders awaiting approval or fulfillment.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: orders not yet received or cancelled.
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['received', 'cancelled']);
    }

    /**
     * Scope: filter by specific status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: complex search and filtering.
     */
    public function scopeFilter($query, array $filters)
    {
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"))
                  ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        return $query;
    }

    /**
     * Calculate total monetary value of order.
     */
    public function calculateTotal(): float
    {
        return (float) ($this->quantity * ($this->product?->unit_price ?? 0));
    }

    /**
     * Check if order can be approved.
     */
    public function canBeApproved(): bool
    {
        return in_array($this->status, ['draft', 'pending']);
    }

    /**
     * Check if order can be received.
     */
    public function canBeReceived(): bool
    {
        return in_array($this->status, ['pending', 'approved', 'sent']);
    }

    public static function generateReference(): string
    {
        return 'PO-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
    }

    public static function generateReferenceNumber(): string
    {
        return static::generateReference();
    }
}
