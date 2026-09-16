<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAccessPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Store is automatically created by migration default store
        $store = Store::defaultStore();

        Branch::firstOrCreate(
            ['code' => 'PLG-01'],
            [
                'name' => 'Cabang Palembang Pusat',
                'address' => 'Jl. Sudirman No. 12',
                'status' => 'active',
            ]
        );

        // Create owner role
        Role::firstOrCreate(
            ['name' => 'owner'],
            [
                'label' => 'Owner',
                'description' => 'Owner with full access',
            ]
        );
    }

    public function test_admin_can_login_via_config_fallback_and_access_laba_rugi(): void
    {
        config([
            'app.admin_email' => 'admin@pempek.com',
            'app.admin_password' => bcrypt('admin123'),
        ]);

        // POST login
        $response = $this->post('/admin/login', [
            'email' => 'admin@pempek.com',
            'password' => 'admin123',
        ]);

        $response->assertRedirect('/admin');
        $this->assertTrue(session('admin_authenticated'));
        $this->assertNotNull(session('admin_user_id'));

        // Access Laba Rugi
        $labaRugi = $this->get('/admin/laporan/keuangan');
        $labaRugi->assertStatus(200);

        // Access Biaya Ops
        $biaya = $this->get('/admin/biaya');
        $biaya->assertStatus(200);

        // Access Pengguna
        $pengguna = $this->get('/admin/pengguna');
        $pengguna->assertStatus(200);
    }

    public function test_existing_authenticated_admin_session_auto_resolves_and_permits_access(): void
    {
        config([
            'app.admin_email' => 'admin@pempek.com',
            'app.admin_password' => bcrypt('admin123'),
        ]);

        $ownerRole = Role::where('name', 'owner')->first();
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@pempek.com',
            'password' => Hash::make('admin123'),
            'role_id' => $ownerRole->id,
            'status' => 'active',
        ]);

        // Simulasikan session admin_authenticated aktif tapi admin_user_id belum terisi
        $response = $this->withSession(['admin_authenticated' => true])
            ->get('/admin/laporan/keuangan');

        $response->assertStatus(200);
    }

    public function test_unauthenticated_request_is_redirected_to_admin_login(): void
    {
        $response = $this->get('/admin/laporan/keuangan');
        $response->assertRedirect('/admin/login');
    }
}
