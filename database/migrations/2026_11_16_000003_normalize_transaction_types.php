<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('transactions')->where('type', 'inbound')->update(['type' => 'in']);
        DB::table('transactions')->where('type', 'outbound')->update(['type' => 'out']);
    }

    public function down(): void
    {
        DB::table('transactions')->where('type', 'in')->update(['type' => 'inbound']);
        DB::table('transactions')->where('type', 'out')->update(['type' => 'outbound']);
    }
};
