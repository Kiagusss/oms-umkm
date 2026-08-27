<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Cek ongkir via RajaOngkir (Komerce) — paket Starter GRATIS 100 hit/hari,
 * 17 ekspedisi domestik. Key dari https://rajaongkir.com (dashboard).
 *
 * ponytail: single-provider, parser defensif tanpa model khusus. Kalau butuh
 * multi-provider atau internasional, baru pecah jadi interface + driver.
 */
class OngkirService
{
    private const BASE = 'https://rajaongkir.komerce.id/api/v1';

    public function configured(): bool
    {
        return (bool) $this->key();
    }

    /**
     * Cari area tujuan (kota/kab/kecamatan) untuk dropdown.
     * @return array<int, array{code: string, label: string}>
     */
    public function searchAreas(string $keyword): array
    {
        if (! $this->configured() || mb_strlen(trim($keyword)) < 3) {
            return [];
        }

        return Cache::remember('ongkir:area:' . md5(mb_strtolower($keyword)), now()->addHours(6), function () use ($keyword) {
            $resp = $this->http()->get(self::BASE . '/destination/domestic-destination', [
                'search' => $keyword, 'limit' => 10, 'offset' => 0,
            ]);

            return collect($resp->json('data') ?? [])->map(fn ($a) => [
                // bentuk respons komunitas: {id, text} — fallback ke field alternatif
                'code' => (string) ($a['id'] ?? ''),
                'label' => trim((string) ($a['text'] ?? $a['label'] ?? '')),
            ])->filter(fn ($a) => $a['code'] !== '' && $a['label'] !== '')->take(10)->values()->all();
        });
    }

    /**
     * Tarif semua layanan kurir origin -> destination untuk berat tertentu (gram).
     * @return array<int, array{courier: string, service: string, description: string, cost: int, etd: string}>
     */
    public function rates(string $destinationAreaId, int $weightGram): array
    {
        if (! $this->configured()) {
            throw new \RuntimeException('Cek ongkir belum dikonfigurasi (RAJAONGKIR_API_KEY kosong).');
        }
        $weight = max(1, min($weightGram, 30000)); // 1 g – 30 kg

        return Cache::remember(
            'ongkir:rate:' . $this->originId() . ":{$destinationAreaId}:{$weight}",
            now()->addHours(6),
            function () use ($destinationAreaId, $weight) {
                try {
                    $resp = $this->http()->asForm()->post(self::BASE . '/calculate/domestic-cost', [
                        'origin' => $this->originId(),
                        'destination' => $destinationAreaId,
                        'weight' => $weight,
                        'courier' => config('services.ongkir.couriers', 'jne:jnt:sicepat'),
                    ]);
                } catch (ConnectionException) {
                    throw new \RuntimeException('Gagal menghubungi layanan ongkir, coba lagi nanti.');
                }

                if ($resp->failed()) {
                    throw new \RuntimeException('Area tujuan tidak dikenali atau layanan ongkir bermasalah.');
                }

                return collect($resp->json('data') ?? [])->map(fn ($p) => [
                    // contoh respons: {name:"JNE", code:"jne", service:"REG", description:"Reguler", cost:19000, etd:"2 day"}
                    'courier' => (string) ($p['name'] ?? strtoupper((string) ($p['code'] ?? ''))),
                    'service' => (string) ($p['service'] ?? ''),
                    'description' => (string) ($p['description'] ?? ''),
                    'cost' => (int) round((float) ($p['cost'] ?? 0)),
                    'etd' => trim((string) ($p['etd'] ?? '')),
                ])->filter(fn ($r) => $r['cost'] > 0)->values()->all();
            }
        );
    }

    private function key(): ?string
    {
        return config('services.ongkir.api_key');
    }

    /** Header auth gaya Komerce: header 'key' biasa (bukan Bearer). */
    private function http(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withHeaders(['key' => $this->key()])->timeout(20);
    }

    private function originId(): string
    {
        return Cache::rememberForever('ongkir:origin', function () {
            $resp = $this->http()->get(self::BASE . '/destination/domestic-destination', [
                'search' => config('services.ongkir.origin_postal', 'Palembang'),
                'limit' => 1, 'offset' => 0,
            ]);

            return (string) ($resp->json('data.0.id') ?? '');
        }) ?: '';
    }
}
