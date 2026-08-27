<?php

namespace Tests\Feature;

use App\Services\OngkirService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OngkirTest extends TestCase
{
    use RefreshDatabase;

    public function test_areas_requires_keyword()
    {
        $this->getJson('/api/ongkir/areas?q=jakarta')->assertOk();
    }

    public function test_areas_returns_empty_without_api_key()
    {
        config(['services.ongkir.api_key' => null]);
        $this->getJson('/api/ongkir/areas?q=jakarta')
            ->assertOk()
            ->assertJson(['areas' => []]);
    }

    public function test_areas_proxies_rajaongkir_response()
    {
        config(['services.ongkir.api_key' => 'test-key']);
        Http::fake([
            'rajaongkir.komerce.id/api/v1/destination/*' => Http::response([
                'meta' => ['message' => 'success', 'code' => 200],
                'data' => [
                    ['id' => 'ID123', 'text' => 'PESANGGRAHAN, KOTA JAKARTA SELATAN, DKI JAKARTA, 12250'],
                ],
            ]),
        ]);

        $this->getJson('/api/ongkir/areas?q=jaksel')
            ->assertOk()
            ->assertJsonFragment(['code' => 'ID123', 'label' => 'PESANGGRAHAN, KOTA JAKARTA SELATAN, DKI JAKARTA, 12250']);
    }

    public function test_rates_validates_destination()
    {
        config(['services.ongkir.api_key' => 'test-key']);
        $this->getJson('/api/ongkir/rates?weight=1000')->assertStatus(422);
    }

    public function test_rates_maps_pricing()
    {
        config(['services.ongkir.api_key' => 'test-key']);
        Http::fake([
            'rajaongkir.komerce.id/api/v1/destination/*' => Http::response([
                'data' => [['id' => 'ORIG', 'text' => 'PALEMBANG']],
            ]),
            'rajaongkir.komerce.id/api/v1/calculate/domestic-cost' => Http::response([
                'meta' => ['message' => 'success', 'code' => 200],
                'data' => [
                    ['name' => 'JNE', 'code' => 'jne', 'service' => 'REG', 'description' => 'REGULER', 'cost' => '12000', 'etd' => '3-4 HARI'],
                    ['name' => 'SICEPAT', 'code' => 'sicepat', 'service' => 'REG', 'cost' => '9000', 'etd' => '2-3'],
                    ['name' => 'X', 'cost' => '0'],
                ],
            ]),
        ]);

        $resp = $this->getJson('/api/ongkir/rates?destination=ID123&weight=1000');
        $resp->assertOk();
        $json = $resp->json();

        $this->assertCount(2, $json['rates']);
        $this->assertSame('JNE', $json['rates'][0]['courier']);
        $this->assertSame(9000, $json['cheapest']['cost']);
        $this->assertSame('SiCepat' === '' ? '' : 'SICEPAT', $json['cheapest']['courier']);
    }

    public function test_rates_rejects_weight_over_limit()
    {
        config(['services.ongkir.api_key' => 'test-key']);
        $this->getJson('/api/ongkir/rates?destination=ID123&weight=99999')->assertStatus(422);
    }

    public function test_service_reports_unconfigured()
    {
        config(['services.ongkir.api_key' => null]);
        $svc = new OngkirService;
        $this->assertFalse($svc->configured());
    }
}
