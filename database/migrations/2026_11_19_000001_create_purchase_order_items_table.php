<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->timestamps();
            $table->index(['purchase_order_id', 'product_id']);
        });

        DB::table('purchase_orders')
            ->join('products', 'purchase_orders.product_id', '=', 'products.id')
            ->select('purchase_orders.id', 'purchase_orders.product_id', 'purchase_orders.quantity', 'products.unit_price')
            ->orderBy('purchase_orders.id')
            ->chunk(200, function ($orders): void {
                $now = now();
                DB::table('purchase_order_items')->insert(
                    $orders->map(fn ($order) => [
                        'purchase_order_id' => $order->id,
                        'product_id' => $order->product_id,
                        'quantity' => $order->quantity,
                        'unit_price' => $order->unit_price,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all()
                );
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
