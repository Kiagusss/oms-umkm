<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Banner;
use App\Models\Faq;
use App\Models\Package;
use App\Models\Product;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $waNumber = config('app.wa_number', '6281234567890');
        $waLink = "https://wa.me/{$waNumber}?text=" . urlencode('Halo, saya ingin memesan Pempek Palembang.');

        $data = [
            'banners' => Banner::all(),
            'products' => Product::orderBy('ord')->get(),
            'packages' => Package::orderBy('ord')->get(),
            'testimonials' => Testimonial::all(),
            'faqs' => Faq::all(),
            'articles' => Article::orderBy('date', 'desc')->get(),
            'waNumber' => $waNumber,
            'waLink' => $waLink,
            'seoTitle' => 'Pempek Palembang — Pempek Asli Palembang, Lezat & Fresh Setiap Hari',
            'seoDescription' => 'Pempek asli Palembang dibuat fresh setiap hari dari ikan tenggiri pilihan dengan resep turun-temurun 3 generasi. Tanpa pengawet, pengiriman cepat ke seluruh Indonesia. Pesan via WhatsApp!',
            'metaKeywords' => 'pempek palembang, pempek asli palembang, pempek kapal selam, pempek lenjer, pempek adaan, pempek frozen, makanan khas palembang, oleh oleh palembang, pempek online, jual pempek',
            'schemaJsonLd' => json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'Restaurant',
                'name' => config('app.name', 'Pempek Palembang'),
                'image' => asset('images/hero-pempek.png'),
                'servesCuisine' => 'Pempek Palembang',
                'priceRange' => 'Rp 10.000 - Rp 150.000',
                'telephone' => '+' . $waNumber,
                'url' => url('/'),
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressLocality' => 'Palembang',
                    'addressRegion' => 'Sumatera Selatan',
                    'addressCountry' => 'ID',
                ],
                'areaServed' => 'Indonesia',
                'sameAs' => collect([
                    config('app.instagram'),
                    config('app.facebook'),
                    config('app.tiktok'),
                ])->filter()->values()->all(),
            ], JSON_UNESCAPED_SLASHES),
        ];

        // Visitor tracker — hash IP, privasi
        try {
            \App\Models\PageView::create([
                'path' => '/',
                'ip' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
            ]);
        } catch (\Throwable $e) {
            // jangan gagalkan halaman karena tracker
        }

        return view('home', $data);
    }
}
