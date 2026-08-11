<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PosHoldResumeViewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withSession(['admin_authenticated' => true]);
    }

    #[Test]
    public function unauthenticated_user_is_redirected_to_login(): void
    {
        session()->forget('admin_authenticated');

        $this->get(route('admin.pos'))->assertRedirect(route('admin.login'));
    }

    #[Test]
    public function pos_view_renders_hold_and_resume_ui(): void
    {
        Product::factory()->create([
            'name'  => 'Pempek Kapal Selam',
            'price' => 15000,
            'stock' => 10,
        ]);

        $response = $this->get(route('admin.pos'));

        $response->assertOk()
            ->assertSee('Tahan Keranjang')
            ->assertSee('Keranjang Ditahan')
            ->assertSee('pempek_pos_held_carts')
            ->assertSee('Pempek Kapal Selam');
    }
}
