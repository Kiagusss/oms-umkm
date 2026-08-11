<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PosCheckoutTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function checkout_creates_order_and_returns_json(): void
    {
        $product = Product::factory()->create([
            'price' => 10000,
            'stock' => 10,
        ]);

        $response = $this->postJson('/api/pos-checkout', [
            'customer_name'     => 'Budi',
            'customer_whatsapp' => '081234567890',
            'items'             => [['id' => $product->id, 'quantity' => 2]],
            'payment_method'    => 'Tunai',
            'cash_received'     => 25000,
        ]);

        $response->assertOk()
                 ->assertJsonFragment(['ok' => true])
                 ->assertJsonStructure([
                     'ok', 'order_id', 'total', 'payment_method',
                     'cash_received', 'change_amount',
                 ]);

        $this->assertDatabaseHas('orders', [
            'name'           => 'Budi',
            'payment_method' => 'Tunai',
            'status'         => 'completed',
        ]);
    }

    #[Test]
    public function checkout_rejects_empty_cart(): void
    {
        $response = $this->postJson('/api/pos-checkout', [
            'customer_name'  => 'Budi',
            'items'          => [],
            'payment_method' => 'Tunai',
        ]);

        $response->assertUnprocessable()
                 ->assertJsonValidationErrors('items');
    }

    #[Test]
    public function checkout_rejects_insufficient_stock(): void
    {
        $product = Product::factory()->create([
            'price' => 10000,
            'stock' => 1,
        ]);

        $response = $this->postJson('/api/pos-checkout', [
            'customer_name'  => 'Budi',
            'items'          => [['id' => $product->id, 'quantity' => 5]],
            'payment_method' => 'Tunai',
        ]);

        $response->assertStatus(422)
                 ->assertJson(['error' => 'Stok ' . $product->name . ' tidak mencukupi (tersedia: 1)']);
    }
}
