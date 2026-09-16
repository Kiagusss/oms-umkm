<?php

namespace App\Services\Import\Importers;

use App\Services\Import\Contracts\ImporterInterface;
use App\Services\Import\DTO\CategoryData;
use App\Services\Import\DTO\ProductData;
use App\Services\Import\DTO\StoreData;
use App\Services\Import\DTO\VariantData;
use Illuminate\Support\Str;

class JsonImporter implements ImporterInterface
{
    public function validateSource(string $source): array
    {
        $content = file_exists($source) ? file_get_contents($source) : $source;
        $decoded = json_decode($content, true);

        if (!is_array($decoded)) {
            return [
                'valid' => false,
                'type' => 'json',
                'message' => 'Format JSON tidak valid atau struktur rusak.',
                'store' => null,
                'items_count' => 0,
            ];
        }

        $items = isset($decoded['products']) ? $decoded['products'] : (isset($decoded[0]) ? $decoded : []);
        $itemsCount = count($items);

        if ($itemsCount === 0) {
            return [
                'valid' => false,
                'type' => 'json',
                'message' => 'JSON tidak memuat daftar produk.',
                'store' => null,
                'items_count' => 0,
            ];
        }

        $storeName = $decoded['store']['name'] ?? 'Katalog JSON Eksternal';

        return [
            'valid' => true,
            'type' => 'json',
            'message' => 'File JSON valid dengan ' . $itemsCount . ' produk.',
            'store' => new StoreData(name: $storeName),
            'items_count' => $itemsCount,
        ];
    }

    public function fetchAndNormalize(string $source): array
    {
        $content = file_exists($source) ? file_get_contents($source) : $source;
        $decoded = json_decode($content, true);

        if (!is_array($decoded)) {
            return ['store' => null, 'categories' => [], 'products' => []];
        }

        $storeData = null;
        if (isset($decoded['store']) && is_array($decoded['store'])) {
            $s = $decoded['store'];
            $storeData = new StoreData(
                name: $s['name'] ?? 'Toko JSON',
                description: $s['description'] ?? null,
                logo: $s['logo'] ?? null,
                phone: $s['phone'] ?? null,
                whatsapp: $s['whatsapp'] ?? null,
                city: $s['city'] ?? null,
                province: $s['province'] ?? null
            );
        } else {
            $storeData = new StoreData(name: 'Toko JSON Import');
        }

        $rawProducts = isset($decoded['products']) ? $decoded['products'] : (isset($decoded[0]) ? $decoded : []);
        $categoriesMap = [];
        $products = [];

        foreach ($rawProducts as $idx => $p) {
            $catName = $p['category'] ?? $p['category_name'] ?? 'Umum';
            if (!isset($categoriesMap[$catName])) {
                $categoriesMap[$catName] = new CategoryData(
                    name: $catName,
                    slug: Str::slug($catName),
                    icon: 'cube',
                    ord: count($categoriesMap) + 1
                );
            }

            $variants = [];
            if (!empty($p['variants']) && is_array($p['variants'])) {
                foreach ($p['variants'] as $v) {
                    $variants[] = new VariantData(
                        name: $v['name'] ?? 'Varian',
                        price: (int) ($v['price'] ?? $p['price'] ?? 0),
                        sku: $v['sku'] ?? null,
                        stock: (int) ($v['stock'] ?? 10)
                    );
                }
            }

            $products[] = new ProductData(
                name: $p['name'] ?? 'Produk ' . ($idx + 1),
                price: (int) ($p['price'] ?? 10000),
                externalId: (string) ($p['id'] ?? $p['external_id'] ?? ('JSON-' . ($idx + 1))),
                sku: $p['sku'] ?? ('SKU-' . strtoupper(Str::random(6))),
                categoryName: $catName,
                categoryExternalId: Str::slug($catName),
                shortDescription: $p['short_description'] ?? Str::limit($p['description'] ?? '', 100),
                description: $p['description'] ?? null,
                stock: (int) ($p['stock'] ?? 10),
                weight: (int) ($p['weight'] ?? 250),
                thumbnail: $p['thumbnail'] ?? ($p['images'][0] ?? null),
                images: $p['images'] ?? [],
                variants: $variants,
                hasVariants: !empty($variants),
                raw: $p
            );
        }

        return [
            'store' => $storeData,
            'categories' => array_values($categoriesMap),
            'products' => $products,
        ];
    }
}
