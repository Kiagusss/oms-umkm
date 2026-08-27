<?php

namespace Tests\Feature;

use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoucherApplyTest extends TestCase
{
    use RefreshDatabase;

    public function test_applies_a_percentage_voucher()
    {
        Voucher::factory()->create([
            'code' => 'DISCOUNT10',
            'type' => 'percentage',
            'value' => 10,
            'min_order' => 0,
            'max_uses' => null,
            'valid_from' => null,
            'valid_until' => null,
            'is_active' => true,
        ]);

        $response = $this->withSession(['admin_authenticated' => true])->postJson('/api/voucher/apply', [
            'code' => 'DISCOUNT10',
            'total' => 100000,
        ]);

        $response->assertOk();
        $response->assertJson([
            'ok' => true,
            'voucher' => [
                'code' => 'DISCOUNT10',
                'type' => 'percentage',
                'value' => 10,
            ],
            'discount' => 10000,
            'new_total' => 90000,
        ]);
    }

    public function test_applies_a_fixed_voucher()
    {
        Voucher::factory()->create([
            'code' => 'DISCOUNT50K',
            'type' => 'fixed',
            'value' => 50000,
            'min_order' => 0,
            'max_uses' => null,
            'valid_from' => null,
            'valid_until' => null,
            'is_active' => true,
        ]);

        $response = $this->withSession(['admin_authenticated' => true])->postJson('/api/voucher/apply', [
            'code' => 'DISCOUNT50K',
            'total' => 100000,
        ]);

        $response->assertOk();
        $response->assertJson([
            'ok' => true,
            'voucher' => [
                'code' => 'DISCOUNT50K',
                'type' => 'fixed',
                'value' => 50000,
            ],
            'discount' => 50000,
            'new_total' => 50000,
        ]);
    }

    public function test_rejects_voucher_below_min_order()
    {
        Voucher::factory()->create([
            'code' => 'DISCOUNT_MIN',
            'type' => 'percentage',
            'value' => 10,
            'min_order' => 200000,
            'max_uses' => null,
            'valid_from' => null,
            'valid_until' => null,
            'is_active' => true,
        ]);

        $response = $this->withSession(['admin_authenticated' => true])->postJson('/api/voucher/apply', [
            'code' => 'DISCOUNT_MIN',
            'total' => 100000,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('total');
    }

    public function test_rejects_invalid_voucher_code()
    {
        $response = $this->withSession(['admin_authenticated' => true])->postJson('/api/voucher/apply', [
            'code' => 'INVALID',
            'total' => 100000,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('code');
    }

    public function test_rejects_expired_voucher()
    {
        Voucher::factory()->create([
            'code' => 'EXPIRED',
            'type' => 'percentage',
            'value' => 10,
            'min_order' => 0,
            'max_uses' => null,
            'valid_from' => null,
            'valid_until' => now()->subDay(),
            'is_active' => true,
        ]);

        $response = $this->withSession(['admin_authenticated' => true])->postJson('/api/voucher/apply', [
            'code' => 'EXPIRED',
            'total' => 100000,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('code');
    }

    public function test_rejects_voucher_not_yet_valid()
    {
        Voucher::factory()->create([
            'code' => 'FUTURE',
            'type' => 'percentage',
            'value' => 10,
            'min_order' => 0,
            'max_uses' => null,
            'valid_from' => now()->addDay(),
            'valid_until' => null,
            'is_active' => true,
        ]);

        $response = $this->withSession(['admin_authenticated' => true])->postJson('/api/voucher/apply', [
            'code' => 'FUTURE',
            'total' => 100000,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('code');
    }

    public function test_rejects_exhausted_voucher()
    {
        Voucher::factory()->create([
            'code' => 'LIMITED',
            'type' => 'percentage',
            'value' => 10,
            'min_order' => 0,
            'max_uses' => 0,
            'valid_from' => null,
            'valid_until' => null,
            'is_active' => true,
        ]);

        $response = $this->withSession(['admin_authenticated' => true])->postJson('/api/voucher/apply', [
            'code' => 'LIMITED',
            'total' => 100000,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('code');
    }

    public function test_rejects_inactive_voucher()
    {
        Voucher::factory()->create([
            'code' => 'INACTIVE',
            'type' => 'percentage',
            'value' => 10,
            'min_order' => 0,
            'max_uses' => null,
            'valid_from' => null,
            'valid_until' => null,
            'is_active' => false,
        ]);

        $response = $this->withSession(['admin_authenticated' => true])->postJson('/api/voucher/apply', [
            'code' => 'INACTIVE',
            'total' => 100000,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('code');
    }
}