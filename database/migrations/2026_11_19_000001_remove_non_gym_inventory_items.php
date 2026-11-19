<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Clean up non-gym categories, suppliers, brands, and products.
     */
    public function up(): void
    {
        $nonGymCategories = ['Lumber', 'Hardware', 'Tools', 'Electrical', 'Plumbing', 'Paint'];
        $nonGymSuppliers = ['Pacific Lumber & Timber Co.', 'Manila Hardware & Industrial Supply', 'Metro Tool & Equipment Corp.'];
        $nonGymBrands = ['DeWalt', 'Bosch', 'Stanley', 'Boysen', 'Philflex'];
        $nonGymPrefixes = ['LMB-%', 'HDW-%', 'TLS-%', 'ELC-%', 'PLM-%', 'PNT-%'];

        // 1. Identify non-gym product IDs
        $productQuery = DB::table('products')->where(function ($query) use ($nonGymCategories, $nonGymSuppliers, $nonGymPrefixes) {
            $query->whereIn('category', $nonGymCategories)
                ->orWhereIn('supplier', $nonGymSuppliers);

            foreach ($nonGymPrefixes as $prefix) {
                $query->orWhere('sku', 'like', $prefix);
            }
        });

        // Also check category_id and supplier_id
        $categoryIds = DB::table('categories')->whereIn('name', $nonGymCategories)->pluck('id');
        $supplierIds = DB::table('suppliers')->whereIn('name', $nonGymSuppliers)->pluck('id');
        $brandIds = DB::table('brands')->whereIn('name', $nonGymBrands)->pluck('id');

        $allProductIds = $productQuery->pluck('id')
            ->merge(DB::table('products')->whereIn('category_id', $categoryIds)->pluck('id'))
            ->merge(DB::table('products')->whereIn('supplier_id', $supplierIds)->pluck('id'))
            ->merge(DB::table('products')->whereIn('brand_id', $brandIds)->pluck('id'))
            ->unique()
            ->filter();

        if ($allProductIds->isNotEmpty()) {
            // Delete related purchase order items if table exists
            if (Schema::hasTable('purchase_order_items')) {
                DB::table('purchase_order_items')->whereIn('product_id', $allProductIds)->delete();
            }

            // Delete related purchase orders
            if (Schema::hasTable('purchase_orders')) {
                DB::table('purchase_orders')->whereIn('product_id', $allProductIds)->delete();
            }

            // Delete related transactions
            if (Schema::hasTable('transactions')) {
                DB::table('transactions')->whereIn('product_id', $allProductIds)->delete();
            }
            if (Schema::hasTable('inventory_transactions')) {
                DB::table('inventory_transactions')->whereIn('product_id', $allProductIds)->delete();
            }

            // Permanently remove the non-gym products
            DB::table('products')->whereIn('id', $allProductIds)->delete();
        }

        // Delete non-gym categories, suppliers, brands
        DB::table('categories')->whereIn('name', $nonGymCategories)->delete();
        DB::table('suppliers')->whereIn('name', $nonGymSuppliers)->delete();
        DB::table('brands')->whereIn('name', $nonGymBrands)->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No restoration needed as non-gym items were erroneous legacy entries
    }
};
