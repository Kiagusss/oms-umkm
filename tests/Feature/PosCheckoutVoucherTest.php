<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PosCheckoutVoucherTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_applies_percentage_voucher_at_checkout(): void
    {
        $product = Product::factory()->create(['price' => 10000, 'stock' => 10]);
        $voucher = Voucher::factory()->create([
            'code' => 'DISCOUNT10',
            'type' => 'percentage',
            'value' => 10,
            'min_order' => null,
            'max_uses' => 5,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/pos-checkout', [
            'customer_name'  => 'Budi',
            'items'          => [['id' => $product->id, 'quantity' => 2]],
            'payment_method' => 'Tunai',
            'cash_received'  => 18000,
            'voucher_code'   => 'DISCOUNT10',
        ]);

        $response->assertOk()
            ->assertJsonFragment([
                'ok'       => true,
                'total'    => 18000,
                'discount' => 2000,
                'change_amount' => 0,
            ]);

        $this->assertDatabaseHas('orders', [
            'name'       => 'Budi',
            'voucher_id' => $voucher->id,
            'discount'   => 2000,
        ]);

        $this->assertDatabaseHas('vouchers', [
            'id'         => $voucher->id,
            'used_count' => 1,
        ]);
    }

    #[Test]
    public function it_applies_fixed_voucher_at_checkout(): void
    {
        $product = Product::factory()->create(['price' => 10000, 'stock' => 10]);
        $voucher = Voucher::factory()->create([
            'code' => 'DISCOUNT50K',
            'type' => 'fixed',
            'value' => 50000,
            'min_order' => null,
            'max_uses' => null,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/pos-checkout', [
            'customer_name'  => 'Budi',
            'items'          => [['id' => $product->id, 'quantity' => 10]],
            'payment_method' => 'Tunai',
            'cash_received'  => 50000,
            'voucher_code'   => 'DISCOUNT50K',
        ]);

        $response->assertOk()
            ->assertJsonFragment([
                'ok'       => true,
                'total'    => 50000,
                'discount' => 50000,
            ]);
    }

    #[Test]
    public function it_rejects_unknown_voucher_at_checkout(): void
    {
        $product = Product::factory()->create(['price' => 10000, 'stock' => 10]);

        $response = $this->postJson('/api/pos-checkout', [
            'customer_name'  => 'Budi',
            'items'          => [['id' => $product->id, 'quantity' => 1]],
            'payment_method' => 'Tunai',
            'cash_received'  => 10000,
            'voucher_code'   => 'NOPE',
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['error' => 'Kode voucher tidak valid']);

        $this->assertDatabaseCount('orders', 0);
    }

    #[Test]
    public function it_rejects_voucher_when_min_order_not_met(): void
    {
        $product = Product::factory()->create(['price' => 10000, 'stock' => 10]);
        $voucher = Voucher::factory()->create([
            'code' => 'MIN200K',
            'type' => 'percentage',
            'value' => 10,
            'min_order' => 200000,
            'max_uses' => null,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/pos-checkout', [
            'customer_name'  => 'Budi',
            'items'          => [['id' => $product->id, 'quantity' => 1]],
            'payment_method' => 'Tunai',
            'cash_received'  => 10000,
            'voucher_code'   => 'MIN200K',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('orders', 0);
    }

    #[Test]
    public function it_rejects_exhausted_voucher_at_checkout(): void
    {
        $product = Product::factory()->create(['price' => 10000, 'stock' => 10]);
        $voucher = Voucher::factory()->create([
            'code' => 'HABIS',
            'type' => 'percentage',
            'value' => 10,
            'min_order' => null,
            'max_uses' => 1,
            'used_count' => 1,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/pos-checkout', [
            'customer_name'  => 'Budi',
            'items'          => [['id' => $product->id, 'quantity' => 1]],
            'payment_method' => 'Tunai',
            'cash_received'  => 10000,
            'voucher_code'   => 'HABIS',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('orders', 0);
    }
}