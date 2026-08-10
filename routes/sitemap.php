<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

// Sitemap.xml — daftar URL yang layak di-index
Route::get('/sitemap.xml', function () {
    $base = url('/');
    $urls = [$base . '/'];

    $products = Product::where('status', 'active')->get(['slug']);
    foreach ($products as $p) {
        $urls[] = $base . '/produk/' . $p->slug;
    }

    $categories = Category::where('status', 'active')->get(['slug']);
    foreach ($categories as $c) {
        $urls[] = $base . '/kategori/' . $c->slug;
    }

    $articles = Article::where('status', 'published')->get(['slug']);
    foreach ($articles as $a) {
        $urls[] = $base . '/artikel/' . $a->slug;
    }

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($urls as $u) {
        $xml .= "  <url><loc>" . htmlspecialchars($u, ENT_XML1, 'UTF-8') . "</loc></url>\n";
    }
    $xml .= '</urlset>';

    return response($xml, 200, ['Content-Type' => 'application/xml']);
});
