<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OngkirService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Cek ongkir publik: cari area tujuan + daftar tarif kurir.
 * Rate limit 30/menit per IP (cukup untuk pengunjung, tahan abuse).
 */
class OngkirController extends Controller
{
    public function areas(Request $request, OngkirService $ongkir): JsonResponse
    {
        $data = $request->validate(['q' => 'required|string|max:80']);

        if (RateLimiter::tooManyAttempts('ongkir:' . $request->ip(), 30)) {
            return response()->json(['error' => 'Terlalu banyak pencarian. Coba lagi nanti.'], 429);
        }
        RateLimiter::hit('ongkir:' . $request->ip(), 60);

        return response()->json(['areas' => $ongkir->searchAreas($data['q'])]);
    }

    public function rates(Request $request, OngkirService $ongkir): JsonResponse
    {
        $data = $request->validate([
            'destination' => 'required|string|max:64',
            'weight' => 'nullable|integer|min:1|max:30000', // gram; default 1000
        ]);

        if (RateLimiter::tooManyAttempts('ongkir:' . $request->ip(), 30)) {
            return response()->json(['error' => 'Terlalu banyak pencarian. Coba lagi nanti.'], 429);
        }
        RateLimiter::hit('ongkir:' . $request->ip(), 60);

        try {
            $rates = $ongkir->rates($data['destination'], (int) ($data['weight'] ?? 1000));
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        }

        return response()->json([
            'origin' => 'Palembang',
            'rates' => $rates,
            'cheapest' => collect($rates)->sortBy('cost')->first(),
        ]);
    }
}
