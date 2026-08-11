<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HappyHourPosTest extends TestCase
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
    public function pos_products_returns_discounted_prices_during_happy_hour(): void
    {
        $this->enableHappyHour(25);
        Carbon::setTestNow(Carbon::create(2026, 8, 11, 18, 0));

        $product = Product::factory()->create(['price' => 10000, 'stock' => 5]);

        $response = $this->getJson('/api/pos-products');

        $response->assertOk()
                 ->assertJsonFragment(['happy_hour_active' => true])
                 ->assertJsonFragment(['happy_hour_percent' => 25])
                 ->assertJsonFragment([
                     'id' => $product->id,
                     'discounted_price' => 7500,
                     'price' => 10000,
                 ]);
    }

    #[Test]
    public function pos_products_returns_normal_prices_when_inactive(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 11, 10, 0));

        $product = Product::factory()->create(['price' => 10000, 'stock' => 5]);

        $response = $this->getJson('/api/pos-products');

        $response->assertOk()
                 ->assertJsonFragment(['happy_hour_active' => false])
                 ->assertJsonFragment(['happy_hour_percent' => 0])
                 ->assertJsonFragment([
                     'id' => $product->id,
                     'discounted_price' => 10000,
                     'price' => 10000,
                 ]);
    }

    #[Test]
    public function pos_view_renders_happy_hour_badge_and_discounted_price(): void
    {
        $this->enableHappyHour(25);
        Carbon::setTestNow(Carbon::create(2026, 8, 11, 18, 0));

        $product = Product::factory()->create(['price' => 10000, 'stock' => 5]);

        $this->withSession(['admin_authenticated' => true])
            ->get(route('admin.pos'))
            ->assertOk()
            ->assertSee('Happy Hour')
            ->assertSee('7.500', false)
            ->assertSee('happyHourDiscounts');
    }
}
