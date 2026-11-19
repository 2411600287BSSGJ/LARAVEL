<?php

namespace Tests\Feature;

use App\Mail\StaffStockMovementMail;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuditTrailTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;

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
    }

    public function test_audit_trail_is_only_visible_to_administrators(): void
    {
        $response = $this->actingAs($this->admin)->get(route('transactions.index'));
        $response->assertStatus(200);
        $response->assertSee('Inventory Audit Trail');
        $response->assertSee('Export CSV');
        $response->assertSee('Print Ledger');

        $this->actingAs($this->staff)->get(route('transactions.index'))->assertForbidden();
        $this->get(route('api.transactions.recent'))->assertForbidden();
        $this->get(route('reports.index'))->assertForbidden();
        $this->get(route('reports.export.stock-movement'))->assertForbidden();
        $this->get(route('exports.download', 'transactions'))->assertForbidden();
        $this->get(route('export.transactions.csv'))->assertForbidden();
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Audit Trail')
            ->assertDontSee('Recent Stock Movements');
    }

    public function test_audit_trail_edit_page_renders_with_transaction_details(): void
    {
        $product = Product::create([
            'name' => 'Olympic Barbell 20kg',
            'sku' => 'GYM-BAR-20',
            'quantity' => 15,
            'reorder_level' => 5,
            'unit_price' => 1500.00,
        ]);

        $tx = InventoryTransaction::create([
            'product_id' => $product->id,
            'user_id' => $this->staff->id,
            'type' => 'stock_in',
            'quantity' => 10,
            'unit_price' => 1500.00,
            'reference_number' => 'REC-BAR-01',
            'notes' => 'Initial batch receipt',
        ]);

        $response = $this->actingAs($this->admin)->get(route('transactions.edit', $tx));
        $response->assertStatus(200);
        $response->assertSee('Edit Audit Record #' . $tx->id);
        $response->assertSee('Olympic Barbell 20kg');
        $response->assertSee('REC-BAR-01');
    }

    public function test_audit_trail_can_be_updated_and_reconciles_stock(): void
    {
        $product = Product::create([
            'name' => 'Hex Dumbbell 10kg',
            'sku' => 'GYM-DB-10',
            'quantity' => 20,
            'reorder_level' => 5,
            'unit_price' => 800.00,
        ]);

        $tx = InventoryTransaction::create([
            'product_id' => $product->id,
            'user_id' => $this->staff->id,
            'type' => 'stock_in',
            'quantity' => 10,
            'unit_price' => 800.00,
            'reference_number' => 'REC-HEX-01',
            'notes' => 'Initial shipment',
        ]);

        // Product quantity after observer: 20 + 10 = 30
        $this->assertSame(30, $product->fresh()->quantity);

        // Edit transaction: change quantity from 10 to 15 (+5 units)
        $response = $this->actingAs($this->admin)->put(route('transactions.update', $tx), [
            'quantity' => 15,
            'unit_price' => 850.00,
            'reference_number' => 'REC-HEX-01-REV',
            'notes' => 'Corrected count after physical verification',
        ]);

        $response->assertRedirect(route('transactions.show', $tx));
        $response->assertSessionHas('success');

        $freshTx = $tx->fresh();
        $this->assertSame(15, $freshTx->quantity);
        $this->assertSame('REC-HEX-01-REV', $freshTx->reference_number);

        // Product stock should have reconciled by +5: 30 + 5 = 35
        $this->assertSame(35, $product->fresh()->quantity);
    }

    public function test_audit_trail_can_export_csv(): void
    {
        $product = Product::create([
            'name' => 'Whey Protein 2lb',
            'sku' => 'SUP-WHEY-2LB',
            'quantity' => 50,
            'reorder_level' => 10,
            'unit_price' => 1200.00,
        ]);

        InventoryTransaction::create([
            'product_id' => $product->id,
            'user_id' => $this->staff->id,
            'type' => 'stock_in',
            'quantity' => 25,
            'unit_price' => 1200.00,
            'reference_number' => 'REC-WHEY-01',
            'notes' => 'New delivery shipment',
        ]);

        $response = $this->actingAs($this->admin)->get(route('transactions.export.csv'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->actingAs($this->staff)->get(route('transactions.export.csv'))->assertForbidden();
    }

    public function test_printable_audit_trail_and_voucher_render(): void
    {
        $product = Product::create([
            'name' => 'Kettlebell 16kg',
            'sku' => 'GYM-KB-16',
            'quantity' => 12,
            'reorder_level' => 4,
            'unit_price' => 1100.00,
        ]);

        $tx = InventoryTransaction::create([
            'product_id' => $product->id,
            'user_id' => $this->staff->id,
            'type' => 'stock_in',
            'quantity' => 6,
            'unit_price' => 1100.00,
            'reference_number' => 'REC-KB-01',
        ]);

        // Test Ledger Print View
        $ledgerResponse = $this->actingAs($this->admin)->get(route('transactions.print'));
        $ledgerResponse->assertStatus(200);
        $ledgerResponse->assertSee('Official Stock Movement Audit Trail Ledger');
        $ledgerResponse->assertSee('Kettlebell 16kg');

        // Test Voucher Print View
        $voucherResponse = $this->actingAs($this->admin)->get(route('transactions.print.show', $tx));
        $voucherResponse->assertStatus(200);
        $voucherResponse->assertSee('Stock Movement Audit Voucher');
        $voucherResponse->assertSee('Kettlebell 16kg');
        $this->actingAs($this->staff)->get(route('transactions.print'))->assertForbidden();
        $this->get(route('transactions.print.show', $tx))->assertForbidden();
    }

    public function test_staff_encoding_stock_in_sends_email_to_staff_and_admin(): void
    {
        Mail::fake();

        $product = Product::create([
            'name' => 'Creatine Monohydrate 300g',
            'sku' => 'SUP-CRE-300',
            'quantity' => 10,
            'reorder_level' => 5,
            'unit_price' => 750.00,
        ]);

        $response = $this->actingAs($this->staff)->post(route('transactions.store'), [
            'product_id' => $product->id,
            'type' => 'stock_in',
            'quantity' => 20,
            'unit_price' => 750.00,
            'reference_number' => 'REC-CRE-01',
            'notes' => 'Fresh stock delivered',
        ]);

        $response->assertRedirect(route('dashboard'));

        // Assert mail was sent to both admin and staff
        Mail::assertSent(StaffStockMovementMail::class, function ($mail) {
            return $mail->staffUser->id === $this->staff->id &&
                   $mail->hasTo($this->admin->email);
        });

        Mail::assertSent(StaffStockMovementMail::class, function ($mail) {
            return $mail->staffUser->id === $this->staff->id &&
                   $mail->hasTo($this->staff->email);
        });
    }

    public function test_staff_encoding_stock_out_sends_email_to_staff_and_admin(): void
    {
        Mail::fake();

        $product = Product::create([
            'name' => 'Gym Chalk Block',
            'sku' => 'ACC-CHALK-01',
            'quantity' => 30,
            'reorder_level' => 5,
            'unit_price' => 150.00,
        ]);

        $response = $this->actingAs($this->staff)->post(route('transactions.store'), [
            'product_id' => $product->id,
            'type' => 'stock_out',
            'quantity' => 5,
            'unit_price' => 150.00,
            'reference_number' => 'OUT-CHALK-01',
            'notes' => 'Issued to front desk',
        ]);

        $response->assertRedirect(route('dashboard'));

        // Assert mail was sent to both admin and staff
        Mail::assertSent(StaffStockMovementMail::class, function ($mail) {
            return $mail->staffUser->id === $this->staff->id &&
                   $mail->hasTo($this->admin->email);
        });

        Mail::assertSent(StaffStockMovementMail::class, function ($mail) {
            return $mail->staffUser->id === $this->staff->id &&
                   $mail->hasTo($this->staff->email);
        });
    }

    public function test_admin_encoding_does_not_trigger_staff_movement_email(): void
    {
        Mail::fake();

        $product = Product::create([
            'name' => 'Resistance Bands Set',
            'sku' => 'ACC-BAND-SET',
            'quantity' => 20,
            'reorder_level' => 5,
            'unit_price' => 450.00,
        ]);

        $response = $this->actingAs($this->admin)->post(route('transactions.store'), [
            'product_id' => $product->id,
            'type' => 'stock_in',
            'quantity' => 10,
            'unit_price' => 450.00,
            'reference_number' => 'ADMIN-IN-01',
            'notes' => 'Admin direct inventory restock',
        ]);

        $response->assertRedirect(route('transactions.index'));

        // Staff movement mail should not be sent when encoded by admin
        Mail::assertNotSent(StaffStockMovementMail::class);
    }
}
