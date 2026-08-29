<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PublicOrderTest extends TestCase
{
    use RefreshDatabase;

    private function seedProduct(): Product
    {
        return Product::create([
            'name' => 'Pempek Publik', 'slug' => 'pempek-publik', 'price' => 20000,
            'stock' => 10, 'status' => 'active', 'short_description' => 'uji singkat', 'description' => 'uji',
            'sold' => 0,
        ]);
    }

    public function test_public_checkout_qris_creates_pending_order(): void
    {
        $p = $this->seedProduct();

        $res = $this->post('/checkout', [
            'customer_name' => 'Andi',
            'customer_whatsapp' => '0812345678',
            'address' => 'Jl. Test No. 1',
            'items' => [['id' => $p->id, 'quantity' => 2]],
            'payment_method' => 'QRIS',
        ]);

        $order = Order::latest('id')->first();
        $res->assertRedirect(route('pesanan.show', $order));

        $this->assertEquals('pending', $order->status);
        $this->assertEquals('QRIS', $order->payment_method);
        $this->assertEquals('Jl. Test No. 1', $order->address);
        $this->assertEquals(40000, $order->total());
        $this->assertEquals(8, $p->fresh()->stock);
    }

    public function test_public_checkout_requires_name_and_items(): void
    {
        // Validasi gagal → redirect back dengan error session (web stack)
        $this->post('/checkout', [
            'customer_name' => '',
            'customer_whatsapp' => '',
            'address' => '',
            'items' => [],
            'payment_method' => 'QRIS',
        ])->assertSessionHasErrors(['customer_name', 'customer_whatsapp', 'address', 'items']);
    }

    public function test_checkout_rejects_fake_shipping_rate(): void
    {
        $p = $this->seedProduct();

        $res = $this->post('/checkout', [
            'customer_name' => 'Tono',
            'customer_whatsapp' => '0812345678',
            'address' => 'Jl. Test No. 2',
            'items' => [['id' => $p->id, 'quantity' => 1]],
            'payment_method' => 'QRIS',
            'shipping' => ['destination' => 'XXX', 'weight' => 1000, 'courier' => 'HACKER', 'service' => 'GRATIS'],
        ]);

        $res->assertSessionHas('error');
        $this->assertDatabaseMissing('orders', ['name' => 'Tono']);
    }

    public function test_public_qris_settle_requires_valid_token(): void
    {
        $p = $this->seedProduct();
        $order = Order::create([
            'name' => 'Budi', 'whatsapp' => '', 'products' => json_encode([
                ['productId' => $p->id, 'productName' => 'Pempek Publik', 'price' => 20000, 'quantity' => 1],
            ]),
            'date' => now()->toDateString(), 'status' => 'pending',
            'payment_method' => 'QRIS', 'discount' => 0,
        ]);

        // Token salah → 403
        $this->postJson('/api/public/qris/settle', [
            'order_id' => $order->id, 'token' => str_repeat('f', 64),
        ])->assertStatus(403);

        // Token benar → processing
        $token = hash('sha256', $order->id . config('app.key') . $order->created_at);
        $this->postJson('/api/public/qris/settle', [
            'order_id' => $order->id, 'token' => $token,
        ])->assertOk()->assertJsonPath('status', 'processing');

        // Double settle → 409
        $this->postJson('/api/public/qris/settle', [
            'order_id' => $order->id, 'token' => $token,
        ])->assertStatus(409);
    }
}