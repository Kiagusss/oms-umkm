<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HappyHourCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function enableHappyHour(int $percent = 25, string $start = '17:00', string $end = '21:00'): void
    {
        Setting::updateOrCreate(['key' => 'happy_hour_enabled'], ['value' => '1']);
        Setting::updateOrCreate(['key' => 'happy_hour_discount_percent'], ['value' => (string) $percent]);
        Setting::updateOrCreate(['key' => 'happy_hour_start'], ['value' => $start]);
        Setting::updateOrCreate(['key' => 'happy_hour_end'], ['value' => $end]);
    }

    #[Test]
    public function checkout_uses_discounted_price_during_happy_hour(): void
    {
        $this->enableHappyHour(25);
        Carbon::setTestNow(Carbon::create(2026, 8, 11, 18, 0));

        $product = Product::factory()->create([
            'price' => 10000,
            'stock' => 10,
        ]);

        $response = $this->postJson('/api/pos-checkout', [
            'customer_name'  => 'Budi',
            'items'          => [['id' => $product->id, 'quantity' => 2]],
            'payment_method' => 'Tunai',
            'cash_received'  => 20000,
        ]);

        $response->assertOk()
                 ->assertJsonFragment(['total' => 15000])
                 ->assertJsonFragment(['subtotal' => 15000]);

        $this->assertDatabaseHas('orders', [
            'name'   => 'Budi',
            'status' => 'completed',
        ]);

        $order = \App\Models\Order::first();
        $items = json_decode($order->products, true);
        $this->assertSame(7500, $items[0]['price']);
        $this->assertSame(2, $items[0]['quantity']);
    }

    #[Test]
    public function checkout_uses_normal_price_when_happy_hour_inactive(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 11, 10, 0));

        $product = Product::factory()->create([
            'price' => 10000,
            'stock' => 10,
        ]);

        $response = $this->postJson('/api/pos-checkout', [
            'customer_name'  => 'Budi',
            'items'          => [['id' => $product->id, 'quantity' => 2]],
            'payment_method' => 'Tunai',
            'cash_received'  => 25000,
        ]);

        $response->assertOk()
                 ->assertJsonFragment(['total' => 20000]);

        $order = \App\Models\Order::first();
        $items = json_decode($order->products, true);
        $this->assertSame(10000, $items[0]['price']);
    }

    #[Test]
    public function checkout_uses_discounted_price_in_overnight_window(): void
    {
        $this->enableHappyHour(10, '22:00', '02:00');
        Carbon::setTestNow(Carbon::create(2026, 8, 11, 23, 30));

        $product = Product::factory()->create([
            'price' => 10000,
            'stock' => 10,
        ]);

        $response = $this->postJson('/api/pos-checkout', [
            'customer_name'  => 'Budi',
            'items'          => [['id' => $product->id, 'quantity' => 1]],
            'payment_method' => 'Tunai',
            'cash_received'  => 10000,
        ]);

        $response->assertOk()
                 ->assertJsonFragment(['total' => 9000]);
    }
}
