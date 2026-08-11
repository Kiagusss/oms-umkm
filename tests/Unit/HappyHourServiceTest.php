<?php

namespace Tests\Unit;

use App\Models\Setting;
use App\Services\HappyHourService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HappyHourServiceTest extends TestCase
{
    use RefreshDatabase;

    private function setSettings(bool $enabled, int $percent, string $start, string $end): void
    {
        Setting::updateOrCreate(['key' => 'happy_hour_enabled'], ['value' => $enabled ? '1' : '0']);
        Setting::updateOrCreate(['key' => 'happy_hour_discount_percent'], ['value' => (string) $percent]);
        Setting::updateOrCreate(['key' => 'happy_hour_start'], ['value' => $start]);
        Setting::updateOrCreate(['key' => 'happy_hour_end'], ['value' => $end]);
    }

    #[Test]
    public function inactive_when_disabled(): void
    {
        $this->setSettings(false, 25, '17:00', '21:00');
        $now = Carbon::createFromTime(18, 0);

        $this->assertFalse(app(HappyHourService::class)->isActive($now));
        $this->assertSame(0, app(HappyHourService::class)->discountPercent($now));
    }

    #[Test]
    public function inactive_outside_window(): void
    {
        $this->setSettings(true, 25, '17:00', '21:00');
        $now = Carbon::createFromTime(16, 59);

        $this->assertFalse(app(HappyHourService::class)->isActive($now));
    }

    #[Test]
    public function active_inside_window(): void
    {
        $this->setSettings(true, 25, '17:00', '21:00');
        $now = Carbon::createFromTime(18, 30);

        $this->assertTrue(app(HappyHourService::class)->isActive($now));
        $this->assertSame(25, app(HappyHourService::class)->discountPercent($now));
    }

    #[Test]
    public function active_in_overnight_window(): void
    {
        $this->setSettings(true, 10, '22:00', '02:00');

        $this->assertTrue(app(HappyHourService::class)->isActive(Carbon::createFromTime(23, 0)));
        $this->assertTrue(app(HappyHourService::class)->isActive(Carbon::createFromTime(1, 0)));
        $this->assertFalse(app(HappyHourService::class)->isActive(Carbon::createFromTime(21, 59)));
        $this->assertFalse(app(HappyHourService::class)->isActive(Carbon::createFromTime(2, 1)));
    }

    #[Test]
    public function start_is_inclusive_end_is_exclusive(): void
    {
        $this->setSettings(true, 25, '17:00', '21:00');

        $this->assertTrue(app(HappyHourService::class)->isActive(Carbon::createFromTime(17, 0)));
        $this->assertFalse(app(HappyHourService::class)->isActive(Carbon::createFromTime(21, 0)));
    }

    #[Test]
    public function inactive_when_start_equals_end(): void
    {
        $this->setSettings(true, 25, '17:00', '17:00');

        $this->assertFalse(app(HappyHourService::class)->isActive(Carbon::createFromTime(17, 0)));
    }

    #[Test]
    public function inactive_when_settings_missing(): void
    {
        $this->assertFalse(app(HappyHourService::class)->isActive(Carbon::createFromTime(12, 0)));
        $this->assertSame(0, app(HappyHourService::class)->discountPercent(Carbon::createFromTime(12, 0)));
    }

    #[Test]
    public function percent_clamped_to_0_100(): void
    {
        Setting::updateOrCreate(['key' => 'happy_hour_enabled'], ['value' => '1']);
        Setting::updateOrCreate(['key' => 'happy_hour_start'], ['value' => '00:00']);
        Setting::updateOrCreate(['key' => 'happy_hour_end'], ['value' => '23:59']);

        Setting::updateOrCreate(['key' => 'happy_hour_discount_percent'], ['value' => '150']);
        $this->assertSame(100, app(HappyHourService::class)->discountPercent(Carbon::createFromTime(12, 0)));

        Setting::updateOrCreate(['key' => 'happy_hour_discount_percent'], ['value' => '-5']);
        $this->assertSame(0, app(HappyHourService::class)->discountPercent(Carbon::createFromTime(12, 0)));

        Setting::updateOrCreate(['key' => 'happy_hour_discount_percent'], ['value' => 'bukan-angka']);
        $this->assertSame(0, app(HappyHourService::class)->discountPercent(Carbon::createFromTime(12, 0)));
    }

    #[Test]
    public function discounted_price_rounds_correctly(): void
    {
        $service = app(HappyHourService::class);

        $this->assertSame(7500, $service->discountedPrice(10000, 25));
        $this->assertSame(6699, $service->discountedPrice(9999, 33));
        $this->assertSame(10000, $service->discountedPrice(10000, 0));
        $this->assertSame(0, $service->discountedPrice(0, 50));
    }
}
