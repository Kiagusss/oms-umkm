<?php

namespace App\Ai\Tools;

use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * ListProductsTool — baca daftar produk (read-only, aman dipanggil publik).
 *
 * Dipakai oleh {@see \App\Ai\Agents\DiaPempekAdminAgent}. Tidak memerlukan
 * password karena hanya membaca data.
 */
class ListProductsTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'Mengambil daftar produk Pempek Palembang. Bisa difilter berdasarkan kata kunci pencarian dan status aktif/non-aktif. Mengembalikan ringkasan produk: id, nama, slug, harga, status aktif, dan stok.';
    }

    public function handle(Request $request): Stringable|string
    {
        $query = Product::query();

        if ($search = $request['search'] ?? null) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (isset($request['active_only']) && $request['active_only'] === true) {
            $query->where('is_active', true);
        }

        $limit = (int) ($request['limit'] ?? 20);
        $limit = max(1, min($limit, 100));

        $products = $query
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name', 'slug', 'price', 'is_active', 'stock']);

        return (string) json_encode([
            'count' => $products->count(),
            'products' => $products->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'price' => $p->price,
                'is_active' => (bool) $p->is_active,
                'stock' => $p->stock,
            ]),
        ], JSON_UNESCAPED_UNICODE);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()
                ->description('Kata kunci pencarian nama/deskripsi produk. Opsional.'),
            'active_only' => $schema->boolean()
                ->description('Jika true, hanya produk dengan is_active=true. Default true.'),
            'limit' => $schema->integer()
                ->min(1)->max(100)
                ->description('Jumlah maksimum produk yang dikembalikan. Default 20.'),
        ];
    }
}
