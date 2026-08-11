<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Services\SalesReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SalesReportTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(array $items, array $overrides = []): Order
    {
        return Order::create(array_merge([
            'name'     => 'Customer',
            'whatsapp' => '0812',
            'products' => json_encode($items, JSON_UNESCAPED_UNICODE),
            'date'     => now()->toDateString(),
            'status'   => 'completed',
            'discount' => 0,
        ], $overrides));
    }

    private function item(int $id, string $name, int $price, int $quantity): array
    {
        return [
            'productId'   => $id,
            'productName' => $name,
            'price'       => $price,
            'quantity'    => $quantity,
            'thumbnail'   => null,
        ];
    }

    #[Test]
    public function it_calculates_net_revenue_after_voucher_discount(): void
    {
        $this->makeOrder([$this->item(1, 'Pempek Kapal Selam', 10000, 2)], ['discount' => 5000]);

        $report = app(SalesReportService::class);

        $this->assertSame(15000, $report->totalRevenue(Order::all()));
    }

    #[Test]
    public function it_excludes_cancelled_orders_from_revenue_and_count(): void
    {
        $this->makeOrder([$this->item(1, 'Pempek Kapal Selam', 10000, 1)]);
        $this->makeOrder([$this->item(2, 'Pempek Lenjer', 8000, 1)], ['status' => 'cancelled']);

        $report = app(SalesReportService::class);
        $orders = Order::all();

        $this->assertSame(10000, $report->totalRevenue($orders));
        $this->assertSame(1, $report->orderCount($orders));
    }

    #[Test]
    public function it_returns_continuous_7_day_series_with_zero_fill(): void
    {
        $this->makeOrder([$this->item(1, 'Pempek Kapal Selam', 10000, 1)], [
            'date' => now()->toDateString(),
        ]);

        $series = app(SalesReportService::class)->dailyRevenue(Order::all(), 7);

        $this->assertCount(7, $series);
        $this->assertSame(now()->toDateString(), $series[6]['date']);
        $this->assertSame(10000, $series[6]['revenue']);
        $this->assertSame(0, $series[0]['revenue']);
    }

    #[Test]
    public function it_ranks_top_products_by_quantity(): void
    {
        $this->makeOrder([$this->item(1, 'Pempek Kapal Selam', 10000, 1)]);
        $this->makeOrder([$this->item(1, 'Pempek Kapal Selam', 10000, 2)]);
        $this->makeOrder([$this->item(2, 'Pempek Lenjer', 8000, 1)]);

        $top = app(SalesReportService::class)->topProducts(Order::all(), 5);

        $this->assertCount(2, $top);
        $this->assertSame('Pempek Kapal Selam', $top[0]['name']);
        $this->assertSame(3, $top[0]['quantity']);
        $this->assertSame(30000, $top[0]['revenue']);
    }

    #[Test]
    public function it_breaks_down_revenue_by_payment_method(): void
    {
        $this->makeOrder([$this->item(1, 'Pempek Kapal Selam', 10000, 1)], ['payment_method' => 'Tunai']);
        $this->makeOrder([$this->item(2, 'Pempek Lenjer', 8000, 1)], ['payment_method' => 'QRIS']);

        $methods = app(SalesReportService::class)->paymentMethodBreakdown(Order::all());
        $methods = $methods->keyBy('method')->map(fn ($m) => $m['revenue']);

        $this->assertSame(2, $methods->count());
        $this->assertSame(10000, $methods->get('Tunai'));
        $this->assertSame(8000, $methods->get('QRIS'));
    }

    #[Test]
    public function dashboard_shows_sales_report_sections(): void
    {
        $this->withSession(['admin_authenticated' => true]);

        $product = Product::factory()->create(['name' => 'Pempek Kapal Selam', 'price' => 10000, 'stock' => 5]);
        $this->makeOrder([$this->item($product->id, 'Pempek Kapal Selam', 10000, 2)], ['discount' => 2000]);

        $response = $this->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Tren Penjualan 7 Hari');
        $response->assertSee('Produk Terlaris');
        $response->assertSee('Metode Pembayaran');
        $response->assertSee('Pempek Kapal Selam');
        $response->assertSee('Rp18,000');
        $response->assertSee('Diskon: -Rp2,000');
    }
}
