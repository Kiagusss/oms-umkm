<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrisTest extends TestCase
{
    use RefreshDatabase;

    private function seedProduct(): Product
    {
        return Product::create([
            'name' => 'Pempek QRIS', 'slug' => 'pempek-qris', 'price' => 15000,
            'stock' => 10, 'status' => 'active', 'description' => 'uji',
            'short_description' => 'uji',
        ]);
    }

    private function adminSession(): array
    {
        return ['admin_authenticated' => true];
    }

    /** Tamu (tanpa session admin) harus ditolak 401. */
    public function test_guest_cannot_create_or_settle_qris(): void
    {
        $product = $this->seedProduct();
        $order = Order::create([
            'name' => 'Tamu', 'whatsapp' => '', 'products' => json_encode([]), 'date' => now(),
            'status' => 'pending', 'payment_method' => 'QRIS', 'discount' => 0,
        ]);

        $this->postJson('/api/qris/create', ['order_id' => $order->id])->assertStatus(401);
        $this->postJson('/api/qris/settle', ['order_id' => $order->id])->assertStatus(401);
    }

    /** Full flow: checkout QRIS → pending + QR payload → settle → completed. */
    public function test_admin_qris_flow_creates_pending_then_settles(): void
    {
        $product = $this->seedProduct();

        // 1. Checkout QRIS
        $res = $this->withSession($this->adminSession())
            ->postJson('/api/pos-checkout', [
                'customer_name' => 'Pembeli QRIS',
                'items' => [['id' => $product->id, 'quantity' => 2]],
                'payment_method' => 'QRIS',
            ]);
        $res->assertOk()->assertJsonPath('payment_method', 'QRIS');
        $orderId = $res->json('order_id');

        // Order harus pending
        $order = Order::find($orderId);
        $this->assertSame('pending', $order->status);
        $this->assertSame(30000, $order->total());

        // 2. Buat QR payload
        $create = $this->withSession($this->adminSession())
            ->postJson('/api/qris/create', ['order_id' => $orderId]);
        $create->assertOk();
        $payload = $create->json('qris_payload');

        // Payload TLV: tag 54 (amount) harus 0000000030000... cek "054" berisi 30000
        $this->assertStringContainsString('540530000', $payload, 'Tag 54 harus berisi amount 30000');
        $this->assertStringContainsString('010212', $payload, 'Tag 01 harus "12" (dynamic QR)');

        // 3. Settle
        $settle = $this->withSession($this->adminSession())
            ->postJson('/api/qris/settle', ['order_id' => $orderId]);
        $settle->assertOk()->assertJsonPath('status', 'completed');

        $this->assertSame('completed', Order::find($orderId)->status);
    }

    /** Settle dua kali ditolak. */
    public function test_settle_twice_conflicts(): void
    {
        $order = Order::create([
            'name' => 'Dobel', 'whatsapp' => '', 'products' => json_encode([]), 'date' => now(),
            'status' => 'completed', 'payment_method' => 'QRIS', 'discount' => 0,
        ]);

        $this->withSession($this->adminSession())
            ->postJson('/api/qris/settle', ['order_id' => $order->id])
            ->assertStatus(409);
    }
}
