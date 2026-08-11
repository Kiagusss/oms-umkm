<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VoucherAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // CSRF aktif di web routes; nonaktifkan di test agar POST form bisa diuji
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class);
        $this->withSession(['admin_authenticated' => true]);
    }

    #[Test]
    public function unauthenticated_user_is_redirected_to_login(): void
    {
        session()->forget('admin_authenticated');

        $this->get('/admin/voucher')->assertRedirect('/admin/login');
    }

    #[Test]
    public function admin_can_see_voucher_index(): void
    {
        Voucher::factory()->create(['code' => 'HEM10', 'type' => 'percentage', 'value' => 10]);
        Voucher::factory()->create(['code' => 'FIX5K', 'type' => 'fixed', 'value' => 5000]);

        $this->get('/admin/voucher')
            ->assertOk()
            ->assertSee('HEM10')
            ->assertSee('FIX5K')
            ->assertSee('Voucher');
    }

    #[Test]
    public function admin_can_create_percentage_voucher(): void
    {
        $this->post('/admin/voucher', [
            'code'       => 'hem10',
            'type'       => 'percentage',
            'value'      => 10,
            'min_order'  => '',
            'max_uses'   => '',
            'valid_from' => '',
            'valid_until'=> '',
            'is_active'  => '1',
        ])->assertRedirect(route('admin.voucher.index'));

        $this->assertDatabaseHas('vouchers', [
            'code'       => 'HEM10',
            'type'       => 'percentage',
            'value'      => 10,
            'is_active'  => true,
        ]);
    }

    #[Test]
    public function admin_can_create_fixed_voucher(): void
    {
        $this->post('/admin/voucher', [
            'code'       => 'FIX5K',
            'type'       => 'fixed',
            'value'      => 5000,
            'min_order'  => 20000,
            'max_uses'   => 100,
            'is_active'  => '1',
        ])->assertRedirect(route('admin.voucher.index'));

        $this->assertDatabaseHas('vouchers', [
            'code'       => 'FIX5K',
            'type'       => 'fixed',
            'value'      => 5000,
            'min_order'  => 20000,
            'max_uses'   => 100,
        ]);
    }

    #[Test]
    public function duplicate_code_is_rejected(): void
    {
        Voucher::factory()->create(['code' => 'HEM10']);

        $this->post('/admin/voucher', [
            'code'       => 'hem10',
            'type'       => 'percentage',
            'value'      => 10,
        ])->assertSessionHasErrors('code');

        $this->assertDatabaseCount('vouchers', 1);
    }

    #[Test]
    public function zero_value_is_rejected(): void
    {
        $this->post('/admin/voucher', [
            'code'       => 'GRATIS',
            'type'       => 'fixed',
            'value'      => 0,
        ])->assertSessionHasErrors('value');

        $this->assertDatabaseCount('vouchers', 0);
    }

    #[Test]
    public function valid_until_before_valid_from_is_rejected(): void
    {
        $this->post('/admin/voucher', [
            'code'        => 'MUNDUR',
            'type'        => 'percentage',
            'value'       => 10,
            'valid_from'  => '2026-12-31T10:00',
            'valid_until' => '2026-01-01T10:00',
        ])->assertSessionHasErrors('valid_until');

        $this->assertDatabaseCount('vouchers', 0);
    }

    #[Test]
    public function admin_can_update_voucher(): void
    {
        $voucher = Voucher::factory()->create(['code' => 'HEM10', 'type' => 'percentage', 'value' => 10, 'is_active' => true]);

        $this->put('/admin/voucher/' . $voucher->id, [
            'code'       => 'HEM15',
            'type'       => 'percentage',
            'value'      => 15,
            'min_order'  => 50000,
            'max_uses'   => 10,
            'valid_from' => '',
            'valid_until'=> '',
        ])->assertRedirect(route('admin.voucher.index'));

        $this->assertDatabaseHas('vouchers', [
            'id'        => $voucher->id,
            'code'      => 'HEM15',
            'value'     => 15,
            'min_order' => 50000,
            'max_uses'  => 10,
        ]);
    }

    #[Test]
    public function admin_can_delete_voucher(): void
    {
        $voucher = Voucher::factory()->create(['code' => 'HAPUS']);

        $this->delete('/admin/voucher/' . $voucher->id)
            ->assertRedirect(route('admin.voucher.index'));

        $this->assertDatabaseMissing('vouchers', ['id' => $voucher->id]);
    }

    #[Test]
    public function created_voucher_works_in_pos_checkout(): void
    {
        $product = Product::factory()->create(['price' => 10000, 'stock' => 10]);

        // Simulate admin creating voucher via CRUD
        $this->post('/admin/voucher', [
            'code'       => 'PROMO20',
            'type'       => 'percentage',
            'value'      => 20,
            'min_order'  => '',
            'max_uses'   => 5,
            'is_active'  => '1',
        ])->assertRedirect(route('admin.voucher.index'));

        // Voucher valid via apply endpoint
        $this->postJson('/api/voucher/apply', [
            'code'  => 'PROMO20',
            'total' => 20000,
        ])->assertOk()
            ->assertJsonFragment(['discount' => 4000, 'new_total' => 16000]);

        // Checkout POS applies the discount and increments used_count
        $response = $this->postJson('/api/pos-checkout', [
            'customer_name'  => 'Budi',
            'items'          => [['id' => $product->id, 'quantity' => 2]],
            'payment_method' => 'Tunai',
            'cash_received'  => 16000,
            'voucher_code'   => 'PROMO20',
        ]);

        $response->assertOk()
            ->assertJsonFragment([
                'ok'       => true,
                'total'    => 16000,
                'discount' => 4000,
                'change_amount' => 0,
            ]);

        $this->assertDatabaseHas('vouchers', [
            'code'       => 'PROMO20',
            'used_count' => 1,
        ]);
    }

    #[Test]
    public function inactive_voucher_is_rejected_at_checkout(): void
    {
        $product = Product::factory()->create(['price' => 10000, 'stock' => 10]);
        $voucher = Voucher::factory()->create(['code' => 'MATI', 'type' => 'percentage', 'value' => 10, 'is_active' => false]);

        $this->postJson('/api/pos-checkout', [
            'customer_name'  => 'Budi',
            'items'          => [['id' => $product->id, 'quantity' => 1]],
            'payment_method' => 'Tunai',
            'cash_received'  => 10000,
            'voucher_code'   => 'MATI',
        ])->assertStatus(422);

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('vouchers', ['id' => $voucher->id, 'used_count' => 0]);
    }
}
