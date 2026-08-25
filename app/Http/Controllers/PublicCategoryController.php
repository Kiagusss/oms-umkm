<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;

class PublicCategoryController extends Controller
{
    public function show(string $slug)
    {
        $category = Category::where('slug', $slug)->where('status', 'active')->firstOrFail();
        $products = Product::where('category_id', $category->id)
            ->where('status', 'active')
            ->orderBy('ord')
            ->get();

        $title = $category->name . ' — Pempek Palembang';
        $description = $category->description
            ?: 'Koleksi ' . $category->name . ' asli Palembang. Dibuat fresh dari ikan tenggiri pilihan, tanpa pengawet, kirim ke seluruh Indonesia.';

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => $title,
            'description' => $description,
            'url' => url()->current(),
            'mainEntity' => [
                '@type' => 'ItemList',
                'itemListElement' => $products->take(20)->values()->map(fn ($p, $i) => [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'name' => $p->name,
                    'url' => route('produk.show', $p->slug),
                ])->all(),
            ],
        ];

        return view('kategori.show', [
            'category' => $category,
            'products' => $products,
            'seoTitle' => $title,
            'seoDescription' => $description,
            'metaKeywords' => implode(', ', [
                'pempek ' . strtolower($category->name),
                'jual pempek ' . strtolower($category->name),
                'pempek palembang', 'beli pempek online',
            ]),
            'ogType' => 'website',
            'ogTitle' => $title,
            'ogDescription' => $description,
            'ogImage' => $products->first()?->thumbnail ? asset($products->first()->thumbnail) : asset('images/hero-pempek.png'),
            'schemaJsonLd' => json_encode($schema, JSON_UNESCAPED_SLASHES),
        ]);
    }
}
