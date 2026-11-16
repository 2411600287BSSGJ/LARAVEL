<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\ReorderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReorderServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_one_purchase_order_when_stock_is_below_the_reorder_level(): void
    {
        $supplier = Supplier::create(['name' => 'Iron Works']);
        $product = Product::create([
            'name' => 'Dumbbell Set',
            'sku' => 'DB-001',
            'supplier_id' => $supplier->id,
            'quantity' => 3,
            'reorder_level' => 5,
            'unit_price' => 49.99,
        ]);
        $service = new ReorderService;

        $firstOrder = $service->checkReorderPoint($product);
        $secondOrder = $service->checkReorderPoint($product);

        $this->assertNotNull($firstOrder);
        $this->assertSame($firstOrder->id, $secondOrder?->id);
        $this->assertSame(2, $firstOrder->quantity);
        $this->assertSame($supplier->id, $firstOrder->supplier_id);
        $this->assertDatabaseCount('purchase_orders', 1);
    }

    public function test_it_does_not_order_at_or_above_the_reorder_level(): void
    {
        $product = Product::create([
            'name' => 'Protein Powder',
            'sku' => 'PP-001',
            'quantity' => 5,
            'reorder_level' => 5,
            'unit_price' => 29.99,
        ]);

        $this->assertNull((new ReorderService)->checkReorderPoint($product));
        $this->assertDatabaseCount('purchase_orders', 0);
    }

    public function test_supplier_performance_is_the_percentage_of_received_orders(): void
    {
        $supplier = Supplier::create(['name' => 'Gym Supply Co']);
        $product = Product::create([
            'name' => 'Barbell',
            'sku' => 'BB-001',
            'quantity' => 20,
            'reorder_level' => 5,
            'unit_price' => 120,
        ]);

        foreach (['received', 'pending'] as $status) {
            PurchaseOrder::create([
                'reference' => PurchaseOrder::generateReference(),
                'product_id' => $product->id,
                'supplier_id' => $supplier->id,
                'quantity' => 5,
                'status' => $status,
            ]);
        }

        $this->assertSame(50.0, $supplier->calculatePerformance());
    }
}
