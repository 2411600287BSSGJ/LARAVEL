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

class InventoryEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private Supplier $supplier;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Boss',
            'email' => 'admin@gym.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->staff = User::create([
            'name' => 'Staff Worker',
            'email' => 'staff@gym.com',
            'password' => bcrypt('password'),
            'role' => 'staff',
        ]);

        $this->supplier = Supplier::create([
            'name' => 'Titan Gym Equipment Supplies',
            'contact_person' => 'Mark Wilson',
            'email' => 'sales@titanfit.com',
            'phone' => '+63 912 345 6789',
            'address' => 'Pasig City, Metro Manila',
            'lead_time_days' => 5,
        ]);

        $this->product = Product::create([
            'name' => 'Olympic Weight Plate 20kg',
            'sku' => 'PLT-OLY-20',
            'supplier_id' => $this->supplier->id,
            'quantity' => 8,
            'reorder_level' => 10,
            'unit_price' => 2500.00,
        ]);
    }

    public function test_purchase_orders_index_renders_with_filters(): void
    {
        PurchaseOrder::create([
            'reference' => 'PO-TEST-001',
            'product_id' => $this->product->id,
            'supplier_id' => $this->supplier->id,
            'quantity' => 10,
            'status' => 'pending',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('purchase-orders.index'));
        $response->assertStatus(200);
        $response->assertSee('PO-TEST-001');
        $response->assertSee('Titan Gym Equipment Supplies');
        $response->assertSee('Olympic Weight Plate 20kg');
    }

    public function test_can_render_create_purchase_order_form(): void
    {
        $response = $this->actingAs($this->admin)->get(route('purchase-orders.create', ['product_id' => $this->product->id]));
        $response->assertStatus(200);
        $response->assertSee('Create Purchase Order');
        $response->assertSee('Olympic Weight Plate 20kg');
    }

    public function test_can_manually_create_purchase_order(): void
    {
        $response = $this->actingAs($this->admin)->post(route('purchase-orders.store'), [
            'product_id' => $this->product->id,
            'supplier_id' => $this->supplier->id,
            'quantity' => 15,
            'notes' => 'Urgent replenishment for barbell racks',
        ]);

        $po = PurchaseOrder::where('product_id', $this->product->id)->first();
        $this->assertNotNull($po);
        $this->assertSame(15, $po->quantity);
        $this->assertSame('pending', $po->status);
        $response->assertRedirect(route('purchase-orders.show', $po));
    }

    public function test_admin_can_cancel_pending_purchase_order(): void
    {
        $po = PurchaseOrder::create([
            'reference' => 'PO-TEST-CANCEL',
            'product_id' => $this->product->id,
            'supplier_id' => $this->supplier->id,
            'quantity' => 5,
            'status' => 'pending',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('purchase-orders.cancel', $po));
        $response->assertRedirect(route('purchase-orders.show', $po));
        $this->assertSame('cancelled', $po->fresh()->status);
    }

    public function test_can_render_printable_purchase_order(): void
    {
        $po = PurchaseOrder::create([
            'reference' => 'PO-PRINT-TEST',
            'product_id' => $this->product->id,
            'supplier_id' => $this->supplier->id,
            'quantity' => 20,
            'status' => 'pending',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->staff)->get(route('purchase-orders.print', $po));
        $response->assertStatus(200);
        $response->assertSee('Official Procurement Purchase Order');
        $response->assertSee('PO-PRINT-TEST');
        $response->assertSee('Titan Gym Equipment Supplies');
    }

    public function test_supplier_profile_show_page_renders_with_products_and_history(): void
    {
        $response = $this->actingAs($this->staff)->get(route('suppliers.show', $this->supplier));
        $response->assertStatus(200);
        $response->assertSee('Titan Gym Equipment Supplies');
        $response->assertSee('Olympic Weight Plate 20kg');
        $response->assertSee('Pasig City, Metro Manila');
        $response->assertSee('Expected Lead Time');
    }

    public function test_product_barcode_label_view_renders_correctly(): void
    {
        $response = $this->actingAs($this->staff)->get(route('products.barcode', $this->product));
        $response->assertStatus(200);
        $response->assertSee('PLT-OLY-20');
        $response->assertSee('Olympic Weight Plate 20kg');
        $response->assertSee('₱2,500.00');
    }

    public function test_admin_can_approve_purchase_order(): void
    {
        $po = PurchaseOrder::create([
            'reference' => 'PO-APPROVE-TEST',
            'product_id' => $this->product->id,
            'supplier_id' => $this->supplier->id,
            'quantity' => 15,
            'status' => 'pending',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('purchase-orders.approve', $po));
        $response->assertRedirect();
        $this->assertSame('approved', $po->fresh()->status);
        $this->assertEquals($this->admin->id, $po->fresh()->approved_by);
    }

    public function test_admin_can_reject_purchase_order(): void
    {
        $po = PurchaseOrder::create([
            'reference' => 'PO-REJECT-TEST',
            'product_id' => $this->product->id,
            'supplier_id' => $this->supplier->id,
            'quantity' => 15,
            'status' => 'pending',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('purchase-orders.reject', $po), [
            'reason' => 'Duplicate order',
        ]);
        $response->assertRedirect();
        $this->assertSame('rejected', $po->fresh()->status);
    }

    public function test_admin_can_mark_purchase_order_as_sent(): void
    {
        $po = PurchaseOrder::create([
            'reference' => 'PO-SEND-TEST',
            'product_id' => $this->product->id,
            'supplier_id' => $this->supplier->id,
            'quantity' => 15,
            'status' => 'approved',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('purchase-orders.send', $po));
        $response->assertRedirect();
        $this->assertSame('sent', $po->fresh()->status);
    }

    public function test_can_export_supplier_performance_report(): void
    {
        $response = $this->actingAs($this->admin)->get(route('reports.export.supplier-performance'));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
