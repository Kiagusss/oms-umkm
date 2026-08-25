<?php

namespace App\Http\Controllers;

use App\Models\Product;

class PublicProductController extends Controller
{
    public function show(string $slug)
    {
        $product = Product::with('category')->where('slug', $slug)->where('status', 'active')->firstOrFail();

        $title = $product->seo_title ?: $product->name . ' — Pempek Palembang';
        $description = $product->seo_description ?: ($product->short_description ?: 'Pempek ' . $product->name . ' asli Palembang, dibuat fresh dari ikan tenggiri pilihan. Pesan online, kirim ke seluruh Indonesia.');

        $image = $product->thumbnail ? asset($product->thumbnail) : asset('images/hero-pempek.png');

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'image' => $image,
            'description' => $description,
            'sku' => 'PEMPEK-' . $product->id,
            'brand' => ['@type' => 'Brand', 'name' => config('app.name', 'Pempek Palembang')],
            'offers' => [
                '@type' => 'Offer',
                'url' => url()->current(),
                'priceCurrency' => 'IDR',
                'price' => $product->price,
                'availability' => $product->stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                'seller' => ['@type' => 'Organization', 'name' => config('app.name', 'Pempek Palembang')],
            ],
        ];
        if ($product->price_strikethrough) {
            $schema['offers']['priceValidUntil'] = now()->addDays(30)->toDateString();
        }
        if ($product->category) {
            $schema['category'] = $product->category->name;
        }

        return view('produk.show', [
            'product' => $product,
            'related' => Product::where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->where('status', 'active')
                ->limit(4)
                ->get(),
            'seoTitle' => $title,
            'seoDescription' => $description,
            'metaKeywords' => implode(', ', array_filter([
                'pempek ' . strtolower($product->name),
                $product->category?->name ? 'pempek ' . strtolower($product->category->name) : null,
                'pempek palembang', 'beli pempek online', 'pempek frozen',
            ])),
            'ogType' => 'product',
            'ogTitle' => $title,
            'ogDescription' => $description,
            'ogImage' => $image,
            'schemaJsonLd' => json_encode($schema, JSON_UNESCAPED_SLASHES),
        ]);
    }
}
