<?php

namespace App\Providers;

use GuzzleHttp\Psr7\Utils;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Variabel global untuk semua view: nomor WA + link order
        View::share('waNumber', config('app.wa_number', '6281234567890'));
        View::share('waLink', 'https://wa.me/' . config('app.wa_number', '6281234567890') . '?text=' . urlencode('Halo, saya ingin memesan Pempek Palembang.'));

        // SEO defaults — fallback kalau controller tidak mengirim meta sendiri
        View::share('seoTitle', config('app.name', 'Pempek Palembang') . ' — Pempek Asli Palembang, Lezat & Fresh Setiap Hari');
        View::share('seoDescription', 'Pempek asli Palembang dibuat fresh setiap hari dari ikan tenggiri pilihan dengan resep turun-temurun 3 generasi. Tanpa pengawet, pengiriman cepat ke seluruh Indonesia. Pesan via WhatsApp!');
        View::share('metaKeywords', 'pempek palembang, pempek asli palembang, pempek kapal selam, pempek lenjer, pempek adaan, pempek frozen, makanan khas palembang, oleh oleh palembang, pempek online, jual pempek');

        // JSON-LD Organization + WebSite — halaman publik saja, sekali render
        View::composer(['home', 'produk.show', 'kategori.show', 'artikel.index', 'artikel.show'], function ($view) {
            if (empty($view->getData()['schemaJsonLd']) && empty($view->getData()['disableSiteSchema'])) {
                $base = url('/');
                $site = config('app.name', 'Pempek Palembang');
                $view->with('schemaJsonLd', json_encode([
                    '@context' => 'https://schema.org',
                    '@graph' => [
                        [
                            '@type' => 'Organization',
                            '@id' => $base . '/#organization',
                            'name' => $site,
                            'url' => $base,
                            'logo' => asset('images/hero-pempek.png'),
                        ],
                        [
                            '@type' => 'WebSite',
                            '@id' => $base . '/#website',
                            'name' => $site,
                            'url' => $base,
                            'inLanguage' => 'id-ID',
                            'publisher' => ['@id' => $base . '/#organization'],
                        ],
                    ],
                ], JSON_UNESCAPED_SLASHES));
            }
        });

        // Rate limiter untuk endpoint chat AI (20 req/menit per IP)
        RateLimiter::for('chat', fn (Request $request) => Limit::perMinute(20)->by($request->ip()));

        // Patch Content-Type detection untuk OpenAI-compatible endpoint yang
        // mengirim body JSON tapi dengan Content-Type: text/event-stream
        // (beberapa gateway AI non-standard seperti 9router). Tanpa patch
        // ini, Laravel HTTP client →json() return null dan SDK gagal parse.
        // Middleware ini:
        //   1. Pastikan Content-Type 'application/json' agar Laravel wrapper
        //      parse benar.
        //   2. Strip suffix "data: [DONE]" SSE-terminator yang beberapa
        //      gateway lempar gabung di akhir body JSON.
        // Aman untuk semua HTTP call lain karena cuma patch kalau body
        // terlihat seperti JSON OpenAI-compatible.
        Http::globalResponseMiddleware(function ($response) {
            $contentType = $response->getHeaderLine('Content-Type');
            $body = (string) $response->getBody();

            // Strip trailing SSE terminator kalau ada. Pattern umum:
            //   "...}\n\ndata: [DONE]\n\n"
            //   "...}data: [DONE]\n"
            // Cari posisi 'data:' terakhir yang menandakan terminator.
            $cleaned = $body;
            if (($pos = strrpos($body, 'data:')) !== false) {
                $candidate = rtrim(substr($body, 0, $pos));
                // Hanya pakai kalau prefix masih valid JSON shape.
                $trim = ltrim($candidate);
                if ($trim !== '' && ($trim[0] === '{' || $trim[0] === '[')) {
                    // Tutup bracket kalau kebuka tapi tidak tertutup
                    $opens = substr_count($candidate, '{') - substr_count($candidate, '}');
                    $openB = substr_count($candidate, '[') - substr_count($candidate, ']');
                    if ($opens > 0) $candidate .= str_repeat('}', $opens);
                    if ($openB > 0) $candidate .= str_repeat(']', $openB);
                    $cleaned = $candidate;
                }
            }

            // Deteksi Content-Type salah dan body JSON → override header
            $looksJson = function (string $b): bool {
                $t = ltrim($b);
                return $t !== '' && ($t[0] === '{' || $t[0] === '[');
            };

            $shouldPatch = ! str_contains($contentType, 'application/json')
                || $cleaned !== $body;

            if ($shouldPatch && $looksJson($cleaned)) {
                $newBody = $response->getBody();
                if ($newBody->isSeekable()) {
                    $newBody->rewind();
                }
                $newStream = Utils::streamFor($cleaned);
                $patched = $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withHeader('Content-Length', (string) strlen($cleaned))
                    ->withBody($newStream);

                return $patched;
            }

            return $response;
        });
    }
}
