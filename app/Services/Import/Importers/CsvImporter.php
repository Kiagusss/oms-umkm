<?php

namespace App\Services\Import\Importers;

use App\Services\Import\Contracts\ImporterInterface;
use App\Services\Import\DTO\CategoryData;
use App\Services\Import\DTO\ProductData;
use App\Services\Import\DTO\StoreData;
use Illuminate\Support\Str;

class CsvImporter implements ImporterInterface
{
    public function validateSource(string $source): array
    {
        if (file_exists($source)) {
            $content = file_get_contents($source);
        } else {
            $content = $source;
        }

        $lines = array_filter(array_map('trim', explode("\n", $content)));
        if (count($lines) < 2) {
            return [
                'valid' => false,
                'type' => 'csv',
                'message' => 'Format CSV tidak valid atau baris data kurang dari 2 baris (header + item).',
                'store' => null,
                'items_count' => 0,
            ];
        }

        $header = str_getcsv(array_shift($lines));
        $header = array_map('strtolower', array_map('trim', $header));

        if (!in_array('name', $header) && !in_array('nama', $header)) {
            return [
                'valid' => false,
                'type' => 'csv',
                'message' => 'Header CSV wajib memiliki kolom "name" atau "nama".',
                'store' => null,
                'items_count' => 0,
            ];
        }

        return [
            'valid' => true,
            'type' => 'csv',
            'message' => 'File CSV valid dengan ' . count($lines) . ' baris data produk.',
            'store' => new StoreData(name: 'Katalog CSV Eksternal'),
            'items_count' => count($lines),
        ];
    }

    public function fetchAndNormalize(string $source): array
    {
        if (file_exists($source)) {
            $content = file_get_contents($source);
        } else {
            $content = $source;
        }

        $lines = array_filter(array_map('trim', explode("\n", $content)));
        if (empty($lines)) {
            return ['store' => null, 'categories' => [], 'products' => []];
        }

        $headers = str_getcsv(array_shift($lines));
        $headers = array_map('strtolower', array_map('trim', $headers));

        $products = [];
        $categoriesMap = [];

        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }

            $row = str_getcsv($line);
            $data = [];
            foreach ($headers as $i => $h) {
                $data[$h] = $row[$i] ?? null;
            }

            $name = $data['name'] ?? $data['nama'] ?? 'Produk Tanpa Nama';
            $price = (int) preg_replace('/[^\d]/', '', (string) ($data['price'] ?? $data['harga'] ?? '0'));
            $sku = $data['sku'] ?? 'SKU-' . strtoupper(Str::random(6));
            $catName = $data['category'] ?? $data['kategori'] ?? 'Umum';
            $desc = $data['description'] ?? $data['deskripsi'] ?? null;
            $stock = (int) ($data['stock'] ?? $data['stok'] ?? 10);
            $weight = (int) ($data['weight'] ?? $data['berat'] ?? 250);
            $thumbnail = $data['thumbnail'] ?? $data['gambar'] ?? null;

            if (!isset($categoriesMap[$catName])) {
                $categoriesMap[$catName] = new CategoryData(
                    name: $catName,
                    slug: Str::slug($catName),
                    icon: 'cube',
                    ord: count($categoriesMap) + 1
                );
            }

            $products[] = new ProductData(
                name: $name,
                price: $price > 0 ? $price : 10000,
                externalId: $data['id'] ?? $sku,
                sku: $sku,
                categoryName: $catName,
                categoryExternalId: Str::slug($catName),
                shortDescription: Str::limit($desc, 100),
                description: $desc,
                stock: $stock,
                weight: $weight,
                thumbnail: $thumbnail,
                images: $thumbnail ? [$thumbnail] : [],
                raw: $data
            );
        }

        return [
            'store' => new StoreData(name: 'Katalog CSV'),
            'categories' => array_values($categoriesMap),
            'products' => $products,
        ];
    }
}
