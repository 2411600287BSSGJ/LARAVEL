<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\PurchaseOrderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PurchaseOrderNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staff;
    protected Product $product;
    protected Supplier $supplier;

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

        $category = Category::create(['name' => 'Cardio Equipment']);
        $this->supplier = Supplier::create(['name' => 'Commercial Gym Outfitters', 'lead_time_days' => 5]);
        $brand = Brand::create(['name' => 'Concept2']);

        $this->product = Product::create([
            'name' => 'Model D Indoor Rower',
            'sku' => 'GYM-ROW-C2',
            'category_id' => $category->id,
            'supplier_id' => $this->supplier->id,
            'brand_id' => $brand->id,
            'quantity' => 2,
            'reorder_level' => 3,
            'unit_price' => 45000.00,
        ]);
    }

    public function test_staff_creating_purchase_order_notifies_admin(): void
    {
        Notification::fake();

        $this->actingAs($this->staff)->post(route('purchase-orders.store'), [
            'product_id' => $this->product->id,
            'quantity' => 5,
            'supplier_id' => $this->supplier->id,
        ]);

        Notification::assertSentTo(
            $this->admin,
            PurchaseOrderNotification::class,
            function ($notification) {
                return $notification->action === 'created'
                    && $notification->actor->id === $this->staff->id;
            }
        );
    }

    public function test_admin_creating_purchase_order_notifies_staff(): void
    {
        Notification::fake();

        $this->actingAs($this->admin)->post(route('purchase-orders.store'), [
            'product_id' => $this->product->id,
            'quantity' => 3,
            'supplier_id' => $this->supplier->id,
        ]);

        Notification::assertSentTo(
            $this->staff,
            PurchaseOrderNotification::class,
            function ($notification) {
                return $notification->action === 'created'
                    && $notification->actor->id === $this->admin->id;
            }
        );
    }

    public function test_admin_approving_purchase_order_notifies_staff_creator(): void
    {
        Notification::fake();

        $order = PurchaseOrder::create([
            'reference' => PurchaseOrder::generateReference(),
            'product_id' => $this->product->id,
            'supplier_id' => $this->supplier->id,
            'quantity' => 4,
            'status' => 'pending',
            'created_by' => $this->staff->id,
        ]);

        $this->actingAs($this->admin)->post(route('purchase-orders.approve', $order));

        Notification::assertSentTo(
            $this->staff,
            PurchaseOrderNotification::class,
            function ($notification) use ($order) {
                return $notification->action === 'approved'
                    && $notification->purchaseOrder->id === $order->id;
            }
        );
    }

    public function test_admin_rejecting_purchase_order_notifies_staff_creator(): void
    {
        Notification::fake();

        $order = PurchaseOrder::create([
            'reference' => PurchaseOrder::generateReference(),
            'product_id' => $this->product->id,
            'supplier_id' => $this->supplier->id,
            'quantity' => 4,
            'status' => 'pending',
            'created_by' => $this->staff->id,
        ]);

        $this->actingAs($this->admin)->post(route('purchase-orders.reject', $order), [
            'rejection_reason' => 'Vendor out of stock currently.',
        ]);

        Notification::assertSentTo(
            $this->staff,
            PurchaseOrderNotification::class,
            function ($notification) use ($order) {
                return $notification->action === 'rejected'
                    && $notification->purchaseOrder->id === $order->id;
            }
        );
    }

    public function test_admin_receiving_purchase_order_notifies_staff_creator(): void
    {
        Notification::fake();

        $order = PurchaseOrder::create([
            'reference' => PurchaseOrder::generateReference(),
            'product_id' => $this->product->id,
            'supplier_id' => $this->supplier->id,
            'quantity' => 4,
            'status' => 'pending',
            'created_by' => $this->staff->id,
        ]);

        $this->actingAs($this->admin)->post(route('purchase-orders.receive', $order));

        Notification::assertSentTo(
            $this->staff,
            PurchaseOrderNotification::class,
            function ($notification) use ($order) {
                return $notification->action === 'received'
                    && $notification->purchaseOrder->id === $order->id;
            }
        );
    }
}
