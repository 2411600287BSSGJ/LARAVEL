<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_out_records_transaction_and_creates_purchase_order_below_reorder_level(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $supplier = Supplier::create(['name' => 'Strength Supply']);
        $product = Product::create([
            'name' => 'Kettlebell',
            'sku' => 'KB-12',
            'supplier_id' => $supplier->id,
            'quantity' => 6,
            'reorder_level' => 5,
            'unit_price' => 35.00,
        ]);

        $this->actingAs($staff)->post(route('transactions.store'), [
            'product_id' => $product->id,
            'type' => 'outbound',
            'quantity' => 2,
            'reference' => 'ISSUE-01',
        ])->assertRedirect(route('transactions.index'));

        $this->assertSame(4, $product->fresh()->quantity);
        $this->assertDatabaseHas('transactions', [
            'product_id' => $product->id,
            'type' => 'outbound',
            'quantity' => 2,
        ]);
        $this->assertDatabaseHas('purchase_orders', [
            'product_id' => $product->id,
            'supplier_id' => $supplier->id,
            'quantity' => 1,
            'status' => 'pending',
        ]);
    }

    public function test_stock_out_cannot_reduce_inventory_below_zero(): void
    {
        $staff = User::factory()->create();
        $product = Product::create([
            'name' => 'Yoga Mat',
            'sku' => 'YM-01',
            'quantity' => 1,
            'reorder_level' => 0,
            'unit_price' => 15,
        ]);

        $this->actingAs($staff)
            ->from(route('transactions.create'))
            ->post(route('transactions.store'), [
                'product_id' => $product->id,
                'type' => 'outbound',
                'quantity' => 2,
            ])
            ->assertSessionHasErrors('quantity');

        $this->assertSame(1, $product->fresh()->quantity);
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_receiving_a_purchase_order_increases_stock_once(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::create([
            'name' => 'Bench',
            'sku' => 'BN-01',
            'quantity' => 0,
            'reorder_level' => 5,
            'unit_price' => 250,
        ]);
        $order = PurchaseOrder::create([
            'reference' => PurchaseOrder::generateReference(),
            'product_id' => $product->id,
            'quantity' => 5,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->post(route('purchase-orders.receive', $order))
            ->assertRedirect(route('purchase-orders.show', $order));

        $this->assertSame(5, $product->fresh()->quantity);
        $this->assertDatabaseHas('transactions', [
            'purchase_order_id' => $order->id,
            'type' => 'inbound',
            'quantity' => 5,
        ]);

        $this->post(route('purchase-orders.receive', $order))->assertForbidden();
        $this->assertSame(5, $product->fresh()->quantity);
        $this->assertSame(1, InventoryTransaction::count());
    }

    public function test_staff_can_view_inventory_but_cannot_manage_users(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)->get(route('products.index'))->assertOk();
        $this->get(route('users.index'))->assertForbidden();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('users.index'))->assertOk();
    }

    public function test_product_scope_filter_matches_sku_brand_category_and_stock_state(): void
    {
        $brand = Brand::create(['name' => 'Iron Co']);
        $category = Category::create(['name' => 'Weights']);
        Product::create([
            'name' => 'Training Plate',
            'sku' => 'TP-10',
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'quantity' => 2,
            'reorder_level' => 4,
            'unit_price' => 10,
        ]);
        Product::create([
            'name' => 'Rower',
            'sku' => 'RW-01',
            'quantity' => 0,
            'reorder_level' => 2,
            'unit_price' => 400,
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('products.index', ['search' => 'TP-', 'brand_id' => $brand->id, 'category_id' => $category->id, 'stock' => 'low']))
            ->assertOk()
            ->assertSee('Training Plate')
            ->assertDontSee('Rower');
    }

    public function test_csv_export_omits_passwords_and_includes_product_rows(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'password' => 'sensitive-test-password']);
        $product = Product::create([
            'name' => 'Rowing Machine',
            'sku' => 'RM-01',
            'quantity' => 2,
            'reorder_level' => 1,
            'unit_price' => 800,
        ]);

        $productCsv = $this->actingAs($admin)
            ->get(route('exports.download', 'products'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->streamedContent();

        $this->assertStringContainsString('Rowing Machine', $productCsv);
        $this->assertStringContainsString($product->sku, $productCsv);

        $userCsv = $this->get(route('exports.download', 'users'))->assertOk()->streamedContent();
        $this->assertStringNotContainsString('sensitive-test-password', $userCsv);
    }

    public function test_inventory_pages_render_for_an_administrator(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        foreach ([
            'dashboard',
            'products.index',
            'brands.index',
            'categories.index',
            'suppliers.index',
            'transactions.index',
            'purchase-orders.index',
            'reports.index',
            'users.index',
        ] as $route) {
            $this->get(route($route))->assertOk();
        }
    }
}
