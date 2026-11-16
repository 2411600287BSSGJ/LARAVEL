<?php

namespace Tests\Feature;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class HardwareInventorySystemTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@hardware.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->staff = User::create([
            'name' => 'Staff User',
            'email' => 'staff@hardware.com',
            'password' => bcrypt('password'),
            'role' => 'staff',
        ]);
    }

    public function test_login_page_renders_with_custom_branding(): void
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertSee('Gym Inventory');
        $response->assertSee('custom.css');
    }

    public function test_user_can_authenticate_and_access_dashboard(): void
    {
        // With OTP 2FA, POST /login redirects to OTP verify page (not dashboard)
        $response = $this->post(route('login'), [
            'email'    => 'admin@hardware.com',
            'password' => 'password',
        ]);
        $response->assertRedirect(route('otp.show'));
        $this->assertGuest(); // Not yet authenticated — waiting for OTP

        // Simulate OTP completion by using actingAs directly
        $dashboard = $this->actingAs($this->admin)->get(route('dashboard'));
        $dashboard->assertStatus(200);
        $dashboard->assertSee('Inventory Dashboard');
        $dashboard->assertSee('Total Products');
    }

    public function test_product_crud_lifecycle_with_validation(): void
    {
        // 1. Validation rejection on missing fields
        $this->actingAs($this->admin)->post(route('products.store'), [])
            ->assertSessionHasErrors(['name', 'sku', 'quantity', 'reorder_level', 'unit_price']);

        // 2. Create product
        $createResponse = $this->actingAs($this->admin)->post(route('products.store'), [
            'name' => 'Heavy Duty 20oz Claw Hammer',
            'sku' => 'TLS-HMR-20',
            'category' => 'Tools',
            'supplier' => 'Apex Tools',
            'quantity' => 25,
            'reorder_level' => 8,
            'unit_price' => 24.99,
            'description' => 'Drop forged steel construction',
        ]);

        $createResponse->assertRedirect(route('products.index'));
        $createResponse->assertSessionHas('success');

        $product = Product::where('sku', 'TLS-HMR-20')->first();
        $this->assertNotNull($product);
        $this->assertSame(25, $product->quantity);

        // 3. Show product details and history view
        $showResponse = $this->actingAs($this->admin)->get(route('products.show', $product));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Heavy Duty 20oz Claw Hammer');
        $showResponse->assertSee('Transaction History & Running Balance', false);

        // 4. Update product
        $updateResponse = $this->actingAs($this->admin)->put(route('products.update', $product), [
            'name' => 'Updated 20oz Claw Hammer',
            'sku' => 'TLS-HMR-20',
            'category' => 'Tools',
            'supplier' => 'Apex Tools',
            'quantity' => 25,
            'reorder_level' => 10,
            'unit_price' => 27.50,
        ]);

        $updateResponse->assertRedirect(route('products.index'));
        $this->assertSame('Updated 20oz Claw Hammer', $product->fresh()->name);
        $this->assertSame('27.50', (string) $product->fresh()->unit_price);

        // 5. Delete product
        $deleteResponse = $this->actingAs($this->admin)->delete(route('products.destroy', $product));
        $deleteResponse->assertRedirect(route('products.index'));
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_stock_in_transaction_increases_product_quantity(): void
    {
        $product = Product::create([
            'name' => '1/2-in PVC Pipe',
            'sku' => 'PLM-PVC-12',
            'quantity' => 10,
            'reorder_level' => 5,
            'unit_price' => 5.00,
        ]);

        $this->actingAs($this->staff)->post(route('transactions.store'), [
            'product_id' => $product->id,
            'type' => 'stock_in',
            'quantity' => 15,
            'reference_number' => 'REC-1001',
        ])->assertRedirect(route('transactions.index'));

        $this->assertSame(25, $product->fresh()->quantity);
    }

    public function test_stock_out_transaction_decreases_product_quantity(): void
    {
        $product = Product::create([
            'name' => 'Drywall Screws 100pk',
            'sku' => 'HDW-SCR-100',
            'quantity' => 20,
            'reorder_level' => 5,
            'unit_price' => 9.99,
        ]);

        $this->actingAs($this->staff)->post(route('transactions.store'), [
            'product_id' => $product->id,
            'type' => 'stock_out',
            'quantity' => 8,
            'reference_number' => 'SALE-5001',
        ])->assertRedirect(route('transactions.index'));

        $this->assertSame(12, $product->fresh()->quantity);
    }

    public function test_stock_out_exceeding_available_quantity_is_rejected(): void
    {
        $product = Product::create([
            'name' => 'Circular Saw 7-1/4in',
            'sku' => 'TLS-SAW-7',
            'quantity' => 3,
            'reorder_level' => 2,
            'unit_price' => 89.00,
        ]);

        $response = $this->actingAs($this->staff)->post(route('transactions.store'), [
            'product_id' => $product->id,
            'type' => 'stock_out',
            'quantity' => 10, // Exceeds available 3
            'reference_number' => 'EXCEED-01',
        ]);

        $response->assertSessionHasErrors(['quantity']);
        $this->assertSame(3, $product->fresh()->quantity);
    }

    public function test_low_stock_notification_triggered_when_quantity_falls_below_reorder_level(): void
    {
        Notification::fake();

        $product = Product::create([
            'name' => 'Romex Wire 250ft',
            'sku' => 'ELE-RMX-250',
            'quantity' => 8,
            'reorder_level' => 5,
            'unit_price' => 110.00,
        ]);

        // Stock-out 4 units -> remaining 4, which is <= reorder level 5
        $this->actingAs($this->staff)->post(route('transactions.store'), [
            'product_id' => $product->id,
            'type' => 'stock_out',
            'quantity' => 4,
            'reference_number' => 'SO-RMX-01',
        ]);

        $this->assertSame(4, $product->fresh()->quantity);
        Notification::assertSentTo([$this->admin, $this->staff], LowStockNotification::class);
    }

    public function test_stock_adjustment_calculates_difference_and_updates_quantity(): void
    {
        $product = Product::create([
            'name' => 'Paint Brush 2-in',
            'sku' => 'PNT-BRS-2',
            'quantity' => 15, // System count
            'reorder_level' => 5,
            'unit_price' => 6.50,
        ]);

        // Physical count audit discovered 12 units (3 units missing)
        $response = $this->actingAs($this->admin)->post(route('adjustments.store'), [
            'product_id' => $product->id,
            'physical_count' => 12,
            'reason' => 'Physical Count Discrepancy',
            'notes' => 'Annual inventory audit count',
        ]);

        $response->assertRedirect(route('transactions.index'));
        $this->assertSame(12, $product->fresh()->quantity);

        $adjustmentTx = InventoryTransaction::where('product_id', $product->id)
            ->where('type', 'adjustment')
            ->first();

        $this->assertNotNull($adjustmentTx);
        $this->assertSame(-3, (int) $adjustmentTx->quantity);
    }

    public function test_api_endpoints_return_real_time_dashboard_data(): void
    {
        Product::create([
            'name' => 'Douglas Fir 2x4',
            'sku' => 'LUM-24',
            'category' => 'Lumber',
            'quantity' => 50,
            'reorder_level' => 10,
            'unit_price' => 7.00,
        ]);

        $stats = $this->actingAs($this->admin)->getJson(route('api.dashboard.stats'));
        $stats->assertStatus(200);
        $stats->assertJsonStructure([
            'total_products',
            'low_stock_count',
            'out_of_stock_count',
            'total_inventory_value',
            'formatted_inventory_value',
            'last_updated',
        ]);

        $lowStock = $this->actingAs($this->admin)->getJson(route('api.products.low-stock'));
        $lowStock->assertStatus(200);

        $recentTx = $this->actingAs($this->admin)->getJson(route('api.transactions.recent'));
        $recentTx->assertStatus(200);
    }

    public function test_csv_reports_export_correctly(): void
    {
        Product::create([
            'name' => 'Angle Grinder 4-1/2in',
            'sku' => 'TLS-GRN-45',
            'category' => 'Tools',
            'quantity' => 4,
            'reorder_level' => 5, // low stock
            'unit_price' => 59.99,
        ]);

        $this->actingAs($this->admin)
            ->get(route('reports.export.valuation'))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $this->actingAs($this->admin)
            ->get(route('reports.export.low-stock'))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $this->actingAs($this->admin)
            ->get(route('reports.export.stock-movement'))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
