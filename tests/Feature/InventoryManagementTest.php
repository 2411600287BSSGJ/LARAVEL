<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\InventorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
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
            'type' => 'out',
            'quantity' => 2,
            'reference' => 'ISSUE-01',
        ])->assertRedirect(route('transactions.index'));

        $this->assertSame(4, $product->fresh()->quantity);
        $this->assertDatabaseHas('transactions', [
            'product_id' => $product->id,
            'type' => 'out',
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
                'type' => 'out',
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
            'type' => 'in',
            'quantity' => 5,
        ]);

        $this->post(route('purchase-orders.receive', $order))->assertForbidden();
        $this->assertSame(5, $product->fresh()->quantity);
        $this->assertSame(1, Transaction::count());
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
            ->assertDontSee('SKU: RW-01');
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

    public function test_low_stock_products_turn_the_notification_bell_red(): void
    {
        Product::create([
            'name' => 'Alert Test Dumbbells',
            'sku' => 'ALERT-01',
            'quantity' => 2,
            'reorder_level' => 5,
            'unit_price' => 40,
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('btn-danger', false)
            ->assertSee('aria-label="1 low-stock alerts"', false)
            ->assertSee('Alert Test Dumbbells');
    }

    public function test_inventory_seeder_adds_sample_data_idempotently(): void
    {
        Auth::logout();
        $this->seed(DatabaseSeeder::class);
        $initialCount = Product::count();

        $this->assertSame(20, $initialCount);
        $this->assertSame(5, Brand::count());
        $this->assertSame(5, Category::count());
        $this->assertGreaterThan(0, Product::whereColumn('quantity', '<=', 'reorder_level')->count());
        $this->assertDatabaseHas('purchase_orders', ['created_by' => 1]);

        Product::where('sku', 'IP-BN-002')->update(['quantity' => 1]);
        $this->seed(InventorySeeder::class);

        $this->assertSame($initialCount, Product::count());
        $this->assertSame(1, Product::where('sku', 'IP-BN-002')->value('quantity'));
    }

    public function test_transaction_update_and_delete_keep_stock_balanced(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::create([
            'name' => 'Adjustable Bench',
            'sku' => 'ADJ-BENCH',
            'quantity' => 8,
            'reorder_level' => 2,
            'unit_price' => 200,
        ]);
        $transaction = Transaction::create([
            'product_id' => $product->id,
            'user_id' => $admin->id,
            'type' => 'out',
            'quantity' => 2,
        ]);

        $this->actingAs($admin)
            ->put(route('transactions.update', $transaction), [
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => 1,
            ])
            ->assertRedirect(route('transactions.show', $transaction));

        $this->assertSame(11, $product->fresh()->quantity);
        $this->assertDatabaseHas('transactions', ['id' => $transaction->id, 'type' => 'in', 'quantity' => 1]);

        $this->delete(route('transactions.destroy', $transaction))->assertRedirect(route('transactions.index'));
        $this->assertSame(10, $product->fresh()->quantity);
        $this->assertSoftDeleted('transactions', ['id' => $transaction->id]);
    }

    public function test_dashboard_stats_endpoint_returns_chart_data_and_refresh_script(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Cardio']);
        Product::create([
            'name' => 'Elliptical',
            'sku' => 'ELLIPTICAL-1',
            'category_id' => $category->id,
            'quantity' => 0,
            'reorder_level' => 2,
            'unit_price' => 1000,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.stats'))
            ->assertOk()
            ->assertJsonPath('totalProducts', 1)
            ->assertJsonPath('outOfStock', 1)
            ->assertJsonStructure([
                'lowStock',
                'inventoryValue',
                'chartData' => ['categoryLabels', 'categoryStock', 'days', 'inbound', 'outbound'],
            ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('setInterval(updateInventoryStats, 10000)', false);
    }

    public function test_admin_can_complete_product_brand_and_category_crud(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $this->post(route('brands.store'), ['name' => 'Forge Athletics', 'description' => 'Gym hardware'])
            ->assertRedirect(route('brands.index'));
        $brand = Brand::where('name', 'Forge Athletics')->firstOrFail();
        $this->get(route('brands.show', $brand))->assertOk()->assertSee('Forge Athletics');
        $this->put(route('brands.update', $brand), ['name' => 'Forge Athletics', 'description' => 'Updated manufacturer'])
            ->assertRedirect(route('brands.index'));

        $this->post(route('categories.store'), ['name' => 'Strength', 'description' => 'Strength equipment'])
            ->assertRedirect(route('categories.index'));
        $category = Category::where('name', 'Strength')->firstOrFail();
        $this->get(route('categories.show', $category))->assertOk()->assertSee('Strength');
        $this->put(route('categories.update', $category), ['name' => 'Strength', 'description' => 'Updated type'])
            ->assertRedirect(route('categories.index'));

        $this->post(route('products.store'), [
            'name' => 'Power Rack',
            'sku' => 'POWER-RACK-01',
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'quantity' => 10,
            'reorder_level' => 3,
            'unit_price' => 899.99,
        ])->assertRedirect(route('products.index'));
        $product = Product::where('sku', 'POWER-RACK-01')->firstOrFail();
        $this->get(route('products.show', $product))->assertOk()->assertSee('Power Rack');
        $this->get(route('products.edit', $product))->assertOk();

        $this->put(route('products.update', $product), [
            'name' => 'Commercial Power Rack',
            'sku' => 'POWER-RACK-02',
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'reorder_level' => 3,
            'unit_price' => 999.99,
        ])->assertRedirect(route('products.index'));
        $this->assertSame(10, $product->fresh()->quantity);
        $this->assertSame('Commercial Power Rack', $product->fresh()->name);

        $this->delete(route('products.destroy', $product))->assertRedirect(route('products.index'));
        $this->assertSoftDeleted('products', ['id' => $product->id]);
        $this->delete(route('categories.destroy', $category))->assertRedirect(route('categories.index'));
        $this->delete(route('brands.destroy', $brand))->assertRedirect(route('brands.index'));
    }

    public function test_unverified_users_cannot_access_inventory_and_admins_cannot_delete_themselves(): void
    {
        $unverified = User::factory()->unverified()->create();
        $this->actingAs($unverified)
            ->get(route('dashboard'))
            ->assertRedirect(route('verification.notice'));
        $this->get(route('profile.edit'))->assertRedirect(route('verification.notice'));

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->from(route('users.index'))
            ->delete(route('users.destroy', $admin))
            ->assertRedirect(route('users.index'))
            ->assertSessionHasErrors('user');

        $this->from(route('profile.edit'))
            ->delete(route('profile.destroy'), ['password' => 'password'])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors('account');

        $this->assertNotSoftDeleted('users', ['id' => $admin->id]);
    }
}
