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

    public static function generateReference(): string
    {
        return 'PO-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
    }
}
