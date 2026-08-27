<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Endpoint POS/upload/voucher/AI-admin wajib menolak tamu (401).
 * Dipasang bersamaan dengan perbaikan keamanan 26 Agustus 2026.
 */
class ApiSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_pos_products()
    {
        $this->getJson('/api/pos-products')->assertStatus(401);
    }

    public function test_guest_cannot_checkout()
    {
        $this->postJson('/api/pos-checkout', [
            'customer_name' => 'X',
            'items' => [['id' => 1, 'quantity' => 1]],
            'payment_method' => 'Tunai',
        ])->assertStatus(401);
    }

    public function test_guest_cannot_upload_image()
    {
        $this->postJson('/api/upload-image')->assertStatus(401);
    }

    public function test_guest_cannot_apply_voucher()
    {
        $this->postJson('/api/voucher/apply', ['code' => 'X', 'total' => 1000])->assertStatus(401);
    }

    public function test_guest_cannot_use_admin_ai_chat()
    {
        $this->postJson('/api/ai/admin/chat', ['message' => 'hi'])->assertStatus(401);
    }
}
