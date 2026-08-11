<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HappyHourSettingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withSession(['admin_authenticated' => true]);
    }

    private function basePayload(array $overrides = []): array
    {
        return array_merge([
            'site_name' => 'Pempek Palembang',
            'address' => 'Jl. Merdeka 1',
            'whatsapp' => '628123456789',
            'email' => 'toko@pempek.test',
            'operating_hours' => '08:00-20:00',
            'footer_text' => '© Pempek',
            'about_us' => 'Toko pempek legendaris.',
        ], $overrides);
    }

    public function test_admin_can_save_happy_hour_settings(): void
    {
        $response = $this->put(route('admin.pengaturan.update'), $this->basePayload([
            'happy_hour_enabled' => 'on',
            'happy_hour_discount_percent' => '25',
            'happy_hour_start' => '17:00',
            'happy_hour_end' => '21:00',
        ]));

        $response->assertRedirect(route('admin.pengaturan'));

        $this->assertDatabaseHas('settings', ['key' => 'happy_hour_enabled', 'value' => '1']);
        $this->assertDatabaseHas('settings', ['key' => 'happy_hour_discount_percent', 'value' => '25']);
        $this->assertDatabaseHas('settings', ['key' => 'happy_hour_start', 'value' => '17:00']);
        $this->assertDatabaseHas('settings', ['key' => 'happy_hour_end', 'value' => '21:00']);
    }

    public function test_unchecked_happy_hour_enabled_saved_as_zero(): void
    {
        $this->put(route('admin.pengaturan.update'), $this->basePayload([
            'happy_hour_discount_percent' => '25',
            'happy_hour_start' => '17:00',
            'happy_hour_end' => '21:00',
        ]));

        $this->assertDatabaseHas('settings', ['key' => 'happy_hour_enabled', 'value' => '0']);
    }

    public function test_invalid_discount_percent_rejected(): void
    {
        $response = $this->put(route('admin.pengaturan.update'), $this->basePayload([
            'happy_hour_enabled' => 'on',
            'happy_hour_discount_percent' => '101',
        ]));

        $response->assertSessionHasErrors('happy_hour_discount_percent');
        $this->assertDatabaseMissing('settings', ['key' => 'happy_hour_discount_percent']);
    }

    public function test_invalid_time_format_rejected(): void
    {
        $response = $this->put(route('admin.pengaturan.update'), $this->basePayload([
            'happy_hour_enabled' => 'on',
            'happy_hour_discount_percent' => '25',
            'happy_hour_start' => 'jam-5',
            'happy_hour_end' => '21:00',
        ]));

        $response->assertSessionHasErrors('happy_hour_start');
    }

    public function test_update_without_happy_hour_fields_keeps_existing_keys(): void
    {
        Setting::updateOrCreate(['key' => 'happy_hour_enabled'], ['value' => '1']);
        Setting::updateOrCreate(['key' => 'happy_hour_discount_percent'], ['value' => '10']);
        Setting::updateOrCreate(['key' => 'happy_hour_start'], ['value' => '17:00']);
        Setting::updateOrCreate(['key' => 'happy_hour_end'], ['value' => '21:00']);

        $this->put(route('admin.pengaturan.update'), $this->basePayload());

        $this->assertDatabaseHas('settings', ['key' => 'happy_hour_enabled', 'value' => '1']);
        $this->assertDatabaseHas('settings', ['key' => 'happy_hour_discount_percent', 'value' => '10']);
    }

    public function test_settings_page_renders_happy_hour_fields(): void
    {
        Setting::updateOrCreate(['key' => 'happy_hour_enabled'], ['value' => '1']);
        Setting::updateOrCreate(['key' => 'happy_hour_discount_percent'], ['value' => '25']);
        Setting::updateOrCreate(['key' => 'happy_hour_start'], ['value' => '17:00']);
        Setting::updateOrCreate(['key' => 'happy_hour_end'], ['value' => '21:00']);

        $this->get(route('admin.pengaturan'))
            ->assertOk()
            ->assertSee('happy_hour_enabled')
            ->assertSee('happy_hour_discount_percent')
            ->assertSee('happy_hour_start')
            ->assertSee('happy_hour_end')
            ->assertSee('HAPPY HOUR')
            ->assertSee('value="25"', false)
            ->assertSee('value="17:00"', false);
    }
}
