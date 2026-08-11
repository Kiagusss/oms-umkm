<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Carbon;

class HappyHourService
{
    /**
     * Apakah saat ini dalam rentang happy hour (server time).
     * Rentang bisa melewati tengah malam (start > end).
     * Start inklusif, end eksklusif; start == end berarti nonaktif.
     */
    public function isActive(?Carbon $now = null): bool
    {
        $now = $now ?? now();

        if ((string) Setting::where('key', 'happy_hour_enabled')->value('value') !== '1') {
            return false;
        }

        $start = $this->parseTime(Setting::where('key', 'happy_hour_start')->value('value'));
        $end   = $this->parseTime(Setting::where('key', 'happy_hour_end')->value('value'));

        if ($start === null || $end === null) {
            return false;
        }

        if ($start->equalTo($end)) {
            return false;
        }

        $current = $now->copy()->startOfDay()->addHours($now->hour)->addMinutes($now->minute);

        if ($start->lessThan($end)) {
            return $current->gte($start) && $current->lt($end);
        }

        // Overnight: lewat tengah malam
        return $current->gte($start) || $current->lt($end);
    }

    /**
     * Persen diskon happy hour, 0 jika nonaktif. Clamp 0-100.
     */
    public function discountPercent(?Carbon $now = null): int
    {
        if (!$this->isActive($now)) {
            return 0;
        }

        $value = Setting::where('key', 'happy_hour_discount_percent')->value('value');

        if (!is_numeric($value)) {
            return 0;
        }

        return (int) max(0, min(100, (int) $value));
    }

    /**
     * Harga efektif setelah diskon happy hour.
     */
    public function discountedPrice(int $price, int $discountPercent): int
    {
        return max(0, (int) round($price * (100 - $discountPercent) / 100));
    }

    private function parseTime(?string $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        $parsed = Carbon::createFromFormat('H:i', $value);

        return $parsed === false ? null : $parsed;
    }
}
