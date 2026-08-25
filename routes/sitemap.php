<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::get('/robots.txt', function () {
    return response(view('robots'), 200, ['Content-Type' => 'text/plain']);
});

// Sitemap.xml — daftar URL yang layak di-index
Route::get('/sitemap.xml', function () {
    $base = url('/');
    $urls = [[
        'loc' => $base . '/',
        'priority' => '1.0',
        'changefreq' => 'daily',
    ]];

    $products = Product::where('status', 'active')->get(['slug', 'updated_at']);
    foreach ($products as $p) {
        $urls[] = [
            'loc' => $base . '/produk/' . $p->slug,
            'priority' => '0.8',
            'changefreq' => 'weekly',
            'lastmod' => $p->updated_at?->toDateString(),
        ];
    }

    $categories = Category::where('status', 'active')->get(['slug', 'updated_at']);
    foreach ($categories as $c) {
        $urls[] = [
            'loc' => $base . '/kategori/' . $c->slug,
            'priority' => '0.7',
            'changefreq' => 'weekly',
            'lastmod' => $c->updated_at?->toDateString(),
        ];
    }

    $articles = Article::where('status', 'published')->get(['slug', 'updated_at']);
    foreach ($articles as $a) {
        $urls[] = [
            'loc' => $base . '/artikel/' . $a->slug,
            'priority' => '0.6',
            'changefreq' => 'monthly',
            'lastmod' => $a->updated_at?->toDateString(),
        ];
    }

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($urls as $u) {
        $xml .= "  <url>\n";
        $xml .= "    <loc>" . htmlspecialchars($u['loc'], ENT_XML1, 'UTF-8') . "</loc>\n";
        if (!empty($u['lastmod'])) {
            $xml .= "    <lastmod>" . $u['lastmod'] . "</lastmod>\n";
        }
        if (!empty($u['changefreq'])) {
            $xml .= "    <changefreq>" . $u['changefreq'] . "</changefreq>\n";
        }
        if (!empty($u['priority'])) {
            $xml .= "    <priority>" . $u['priority'] . "</priority>\n";
        }
        $xml .= "  </url>\n";
    }
    $xml .= '</urlset>';

    return response($xml, 200, ['Content-Type' => 'application/xml']);
});
