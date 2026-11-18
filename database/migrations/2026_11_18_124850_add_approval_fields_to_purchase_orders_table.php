<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('created_by');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->decimal('subtotal', 12, 2)->nullable()->after('quantity');
            $table->decimal('tax', 12, 2)->nullable()->default(0)->after('subtotal');
            $table->decimal('total_amount', 12, 2)->nullable()->after('tax');
            $table->date('expected_delivery_date')->nullable()->after('received_at');
            $table->string('rejection_reason')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['approved_by', 'approved_at', 'subtotal', 'tax', 'total_amount', 'expected_delivery_date', 'rejection_reason']);
        });
    }
};
