<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SalesOrderTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staff;
    protected Product $inStockProduct;
    protected Product $outOfStockProduct;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $category = Category::create(['name' => 'Free Weights']);
        $supplier = Supplier::create(['name' => 'Strength Supply Direct', 'lead_time_days' => 3]);
        $brand = Brand::create(['name' => 'Rogue Fitness']);

        $this->inStockProduct = Product::create([
            'name' => 'Rubber Hex Dumbbells (50 lbs)',
            'sku' => 'GYM-DB-50',
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'brand_id' => $brand->id,
            'quantity' => 10,
            'reorder_level' => 3,
            'unit_price' => 1200.00,
        ]);

        $this->outOfStockProduct = Product::create([
            'name' => 'Olympic Cerakote Barbell',
            'sku' => 'GYM-BAR-00',
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'brand_id' => $brand->id,
            'quantity' => 0,
            'reorder_level' => 2,
            'unit_price' => 3500.00,
        ]);
    }

    public function test_both_admin_and_staff_can_view_sales_orders(): void
    {
        $this->actingAs($this->staff)
            ->get(route('sales-orders.index'))
            ->assertOk();

        $this->actingAs($this->admin)
            ->get(route('sales-orders.index'))
            ->assertOk();
    }

    public function test_staff_can_create_sales_order_and_it_starts_as_pending(): void
    {
        Notification::fake();

        $response = $this->actingAs($this->staff)->post(route('sales-orders.store'), [
            'customer_name' => 'Jane Member',
            'customer_phone' => '0917-111-2222',
            'product_id' => $this->inStockProduct->id,
            'quantity' => 2,
        ]);

        $this->assertDatabaseHas('sales_orders', [
            'customer_name' => 'Jane Member',
            'product_id' => $this->inStockProduct->id,
            'quantity' => 2,
            'status' => 'pending',
            'created_by' => $this->staff->id,
        ]);

        $order = SalesOrder::where('customer_name', 'Jane Member')->first();
        $response->assertRedirect(route('sales-orders.show', $order));
    }

    public function test_admin_created_sales_order_also_requires_approval(): void
    {
        Notification::fake();

        $this->actingAs($this->admin)->post(route('sales-orders.store'), [
            'customer_name' => 'Club VIP Client',
            'product_id' => $this->inStockProduct->id,
            'quantity' => 1,
        ]);

        $this->assertDatabaseHas('sales_orders', [
            'customer_name' => 'Club VIP Client',
            'status' => 'pending',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_out_of_stock_product_cannot_be_processed_in_sales_order(): void
    {
        $response = $this->actingAs($this->staff)->from(route('sales-orders.create'))->post(route('sales-orders.store'), [
            'customer_name' => 'Eager Customer',
            'product_id' => $this->outOfStockProduct->id,
            'quantity' => 1,
        ]);

        $response->assertRedirect(route('sales-orders.create'));
        $response->assertSessionHasErrors('product_id');

        $this->assertDatabaseMissing('sales_orders', [
            'customer_name' => 'Eager Customer',
        ]);
    }

    public function test_quantity_exceeding_available_stock_is_rejected(): void
    {
        $response = $this->actingAs($this->staff)->from(route('sales-orders.create'))->post(route('sales-orders.store'), [
            'customer_name' => 'Bulk Buyer',
            'product_id' => $this->inStockProduct->id,
            'quantity' => 50, // Available is only 10
        ]);

        $response->assertRedirect(route('sales-orders.create'));
        $response->assertSessionHasErrors('quantity');

        $this->assertDatabaseMissing('sales_orders', [
            'customer_name' => 'Bulk Buyer',
        ]);
    }

    public function test_only_admin_can_approve_sales_order(): void
    {
        Notification::fake();

        $order = SalesOrder::create([
            'order_number' => SalesOrder::generateOrderNumber(),
            'customer_name' => 'Gym Member',
            'product_id' => $this->inStockProduct->id,
            'quantity' => 3,
            'unit_price' => 1200.00,
            'total_amount' => 3600.00,
            'status' => 'pending',
            'created_by' => $this->staff->id,
        ]);

        // Staff tries to approve -> 403 Forbidden
        $this->actingAs($this->staff)
            ->post(route('sales-orders.approve', $order))
            ->assertForbidden();

        $this->assertEquals('pending', $order->fresh()->status);

        // Admin approves -> Success
        $this->actingAs($this->admin)
            ->post(route('sales-orders.approve', $order))
            ->assertRedirect(route('sales-orders.show', $order));

        $order->refresh();
        $this->assertEquals('approved', $order->status);
        $this->assertEquals($this->admin->id, $order->approved_by);
        $this->assertNotNull($order->approved_at);

        // Stock must be deducted: 10 - 3 = 7
        $this->assertEquals(7, $this->inStockProduct->fresh()->quantity);

        // Inventory transaction must be logged
        $this->assertDatabaseHas('transactions', [
            'product_id' => $this->inStockProduct->id,
            'type' => 'stock_out',
            'quantity' => 3,
            'reference_number' => $order->order_number,
        ]);
    }

    public function test_admin_cannot_approve_sales_order_if_product_became_out_of_stock(): void
    {
        $order = SalesOrder::create([
            'order_number' => SalesOrder::generateOrderNumber(),
            'customer_name' => 'Waiting Customer',
            'product_id' => $this->inStockProduct->id,
            'quantity' => 5,
            'unit_price' => 1200.00,
            'total_amount' => 6000.00,
            'status' => 'pending',
            'created_by' => $this->staff->id,
        ]);

        // Deplete stock before admin approves
        $this->inStockProduct->update(['quantity' => 0]);

        $this->actingAs($this->admin)
            ->from(route('sales-orders.show', $order))
            ->post(route('sales-orders.approve', $order))
            ->assertRedirect(route('sales-orders.show', $order))
            ->assertSessionHas('error');

        $this->assertEquals('pending', $order->fresh()->status);
        $this->assertEquals(0, $this->inStockProduct->fresh()->quantity);
    }

    public function test_admin_can_reject_sales_order(): void
    {
        Notification::fake();

        $order = SalesOrder::create([
            'order_number' => SalesOrder::generateOrderNumber(),
            'customer_name' => 'Rejected Customer',
            'product_id' => $this->inStockProduct->id,
            'quantity' => 2,
            'unit_price' => 1200.00,
            'total_amount' => 2400.00,
            'status' => 'pending',
            'created_by' => $this->staff->id,
        ]);

        $this->actingAs($this->admin)->post(route('sales-orders.reject', $order), [
            'rejection_reason' => 'Customer requested cancellation.',
        ])->assertRedirect(route('sales-orders.show', $order));

        $order->refresh();
        $this->assertEquals('rejected', $order->status);
        $this->assertEquals('Customer requested cancellation.', $order->rejection_reason);

        // Stock must not be touched
        $this->assertEquals(10, $this->inStockProduct->fresh()->quantity);
    }
}
