<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StrukExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withSession(['admin_authenticated' => true]);
    }

    protected function makeOrder(array $overrides = []): Order
    {
        return Order::create(array_merge([
            'name' => 'Budi',
            'whatsapp' => '08123456789',
            'products' => json_encode([
                ['name' => 'Pempek Kapal Selam', 'quantity' => 2, 'price' => 15000],
                ['name' => 'Tekwan', 'quantity' => 1, 'price' => 10000],
            ]),
            'notes' => null,
            'date' => '2026-08-11',
            'status' => 'completed',
            'payment_method' => 'cash',
            'cash_received' => 50000,
            'change_amount' => 10000,
        ], $overrides));
    }

    protected function orderItems(Order $order): array
    {
        return is_array($order->products) ? $order->products : json_decode((string) $order->products, true) ?? [];
    }

    public function test_unauthenticated_user_is_redirected_to_login()
    {
        session()->forget('admin_authenticated');
        $order = $this->makeOrder();

        $this->get(route('admin.pesanan.struk', $order))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_export_struk_as_pdf()
    {
        $order = $this->makeOrder();

        $response = $this->get(route('admin.pesanan.struk', $order));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition') ?? '');
        // magic bytes PDF
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_struk_view_contains_order_number_and_total()
    {
        $order = $this->makeOrder();

        $html = view('admin.pesanan.struk', [
            'pesanan' => $order,
            'settings' => [],
            'items' => $this->orderItems($order),
            'subtotal' => $order->total(),
            'discount' => (int) ($order->discount ?? 0),
        ])->render();

        $this->assertStringContainsString('#' . $order->id, $html);
        // total = 2*15000 + 1*10000 = 40000
        $this->assertStringContainsString('40,000', $html);
        $this->assertStringContainsString('Pempek Kapal Selam', $html);
    }

    public function test_struk_view_shows_discount_when_order_has_discount()
    {
        $order = $this->makeOrder(['discount' => 4000]);

        $html = view('admin.pesanan.struk', [
            'pesanan' => $order,
            'settings' => [],
            'items' => $this->orderItems($order),
            'subtotal' => $order->total(),
            'discount' => (int) ($order->discount ?? 0),
        ])->render();

        $this->assertStringContainsString('Diskon', $html);
        // total = 40000 - 4000 = 36000
        $this->assertStringContainsString('36,000', $html);
        $this->assertStringContainsString('Rp4,000', $html);
    }

    public function test_struk_view_shows_store_name_from_settings()
    {
        Setting::create(['key' => 'site_name', 'value' => 'Pempek Palembang Jaya']);

        $order = $this->makeOrder();
        $settings = Setting::pluck('value', 'key')->toArray();

        $html = view('admin.pesanan.struk', [
            'pesanan' => $order,
            'settings' => $settings,
            'items' => $this->orderItems($order),
            'subtotal' => $order->total(),
            'discount' => (int) ($order->discount ?? 0),
        ])->render();

        $this->assertStringContainsString('Pempek Palembang Jaya', $html);
    }
}
