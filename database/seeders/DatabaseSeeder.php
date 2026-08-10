<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    protected string $dataDir;

    public function __construct()
    {
        $this->dataDir = __DIR__ . '/../data';
    }

    public function run(): void
    {
        if (!File::isDirectory($this->dataDir)) {
            File::makeDirectory($this->dataDir, 0755, true);
        }

        $this->seedCategories();
        $this->seedProducts();
        $this->seedPackages();
        $this->seedArticles();
        $this->seedTestimonials();
        $this->seedFaqs();
        $this->seedBanners();
        $this->seedGallery();
        $this->seedOrders();
        $this->seedSettings();
        $this->seedSeo();
        $this->seedAdmin();
    }

    protected function seedAdmin(): void
    {
        // Hanya buat hash jika ADMIN_PASSWORD belum ada di .env.
        // Jangan pernah menimpa hash yang sudah ada.
        $env = File::exists(base_path('.env')) ? File::get(base_path('.env')) : '';
        if (preg_match('/^ADMIN_PASSWORD=/m', $env)) {
            return;
        }
        $password = config('app.admin_password', 'admin123');
        $this->updateEnv('ADMIN_PASSWORD', Hash::make($password));
    }

    protected function updateEnv(string $key, string $value): void
    {
        $path = base_path('.env');
        if (!File::exists($path)) {
            return;
        }
        $lines = File::lines($path)->toArray();
        $found = false;
        foreach ($lines as $i => $line) {
            if (str_starts_with($line, $key . '=')) {
                $lines[$i] = $key . '=' . $value;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $lines[] = $key . '=' . $value;
        }
        File::put($path, implode(PHP_EOL, $lines));
    }

    protected function loadJson(string $file): array
    {
        $path = $this->dataDir . '/' . $file;
        if (File::exists($path)) {
            $content = File::get($path);
            return json_decode($content, true) ?? [];
        }
        return [];
    }

    protected function seedCategories(): void
    {
        $categories = $this->loadJson('categories.json');
        $defaultCategories = [
            ['name' => 'Pempek Panjang', 'slug' => 'pempek-panjang', 'icon' => 'ruler-combined', 'ord' => 1, 'status' => 'active'],
            ['name' => 'Pempek Adaan', 'slug' => 'pempek-adaan', 'icon' => 'circle', 'ord' => 2, 'status' => 'active'],
            ['name' => 'Pempek Ikan Khas', 'slug' => 'pempek-ikan-khas', 'icon' => 'fish', 'ord' => 3, 'status' => 'active'],
        ];

        $allCategories = array_merge($categories, $defaultCategories);
        foreach ($allCategories as $cat) {
            \App\Models\Category::updateOrCreate(
                ['slug' => $cat['slug']],
                [
                    'name' => $cat['name'],
                    'icon' => $cat['icon'] ?? null,
                    'ord' => $cat['order'] ?? $cat['ord'] ?? 0,
                    'status' => $cat['status'] ?? 'active',
                ]
            );
        }
    }

    protected function seedProducts(): void
    {
        $products = $this->loadJson('products.json');
        $categories = \App\Models\Category::pluck('id', 'slug')->toArray();
        foreach ($products as $p) {
            $catSlug = $p['categorySlug'] ?? null;
            $categoryId = $catSlug && isset($categories[$catSlug]) ? $categories[$catSlug] : ($p['categoryId'] ?? $p['category_id'] ?? null);
            \App\Models\Product::updateOrCreate(
                ['slug' => $p['slug']],
                [
                    'name' => $p['name'] ?? '',
                    'price' => $p['price'] ?? 0,
                    'category_id' => $categoryId,
                    'short_description' => $p['shortDescription'] ?? $p['short_description'] ?? '',
                    'description' => $p['fullDescription'] ?? $p['full_description'] ?? '',
                    'composition' => $p['composition'] ?? null,
                    'stock' => $p['stock'] ?? 0,
                    'weight' => $p['weight'] ?? 0,
                    'thumbnail' => $p['thumbnail'] ?? null,
                    'images' => json_encode($p['images'] ?? []),
                    'is_best_seller' => $p['isBestSeller'] ?? $p['is_best_seller'] ?? false,
                    'is_featured' => $p['isFeatured'] ?? $p['is_featured'] ?? false,
                    'ord' => $p['order'] ?? $p['ord'] ?? 0,
                    'status' => $p['status'] ?? 'active',
                ]
            );
        }
    }

    protected function seedPackages(): void
    {
        $packages = $this->loadJson('packages.json');
        $defaultPackages = [
            [
                'name' => 'Paket Hemat Keluarga',
                'description' => 'Paket hemat untuk 4-6 orang',
                'price' => 85000,
                'original_price' => 95000,
                'badge' => 'Recommended',
                'is_featured' => true,
                'ord' => 1,
                'status' => 'active',
                'items' => json_encode([
                    ['quantity' => 4, 'name' => 'Pempek Lenjer'],
                    ['quantity' => 2, 'name' => 'Pempek Adaan'],
                    ['quantity' => 1, 'name' => 'Sayur Asin'],
                ]),
            ],
            [
                'name' => 'Paket Hemat Hemat',
                'description' => 'Paket hemat untuk 2-3 orang',
                'price' => 45000,
                'original_price' => 50000,
                'badge' => null,
                'is_featured' => false,
                'ord' => 2,
                'status' => 'active',
                'items' => json_encode([
                    ['quantity' => 2, 'name' => 'Pempek Lenjer'],
                    ['quantity' => 1, 'name' => 'Pempek Adaan'],
                ]),
            ],
        ];

        $allPackages = array_merge($packages, $defaultPackages);
        foreach ($allPackages as $pkg) {
            \App\Models\Package::updateOrCreate(
                ['name' => $pkg['name']],
                $pkg
            );
        }
    }

    protected function seedArticles(): void
    {
        $articles = $this->loadJson('articles.json');
        foreach ($articles as $a) {
            \App\Models\Article::updateOrCreate(
                ['slug' => $a['slug']],
                [
                    'title' => $a['title'] ?? $a['title'] ?? '',
                    'thumbnail' => $a['thumbnail'] ?? null,
                    'category' => $a['category'] ?? '',
                    'content' => $a['content'] ?? $a['fullDescription'] ?? '',
                    'author' => $a['author'] ?? null,
                    'date' => $a['date'] ?? now()->format('Y-m-d'),
                    'seo_title' => $a['seoTitle'] ?? $a['seo_title'] ?? null,
                    'seo_description' => $a['seoDescription'] ?? $a['seo_description'] ?? null,
                    'meta_keywords' => $a['metaKeywords'] ?? $a['meta_keywords'] ?? null,
                    'status' => $a['status'] ?? 'published',
                ]
            );
        }
    }

    protected function seedTestimonials(): void
    {
        $testimonials = $this->loadJson('testimonials.json');
        foreach ($testimonials as $t) {
            \App\Models\Testimonial::updateOrCreate(
                ['name' => $t['name']],
                [
                    'avatar' => $t['photo'] ?? $t['avatar'] ?? null,
                    'kombinasi' => $t['kombinasi'] ?? null,
                    'rating' => $t['rating'] ?? 5,
                    'comment' => $t['comment'] ?? '',
                    'date' => $t['date'] ?? now()->format('Y-m-d'),
                    'status' => $t['status'] ?? 'active',
                    'ord' => $t['order'] ?? $t['ord'] ?? 0,
                ]
            );
        }
    }

    protected function seedFaqs(): void
    {
        $faqs = $this->loadJson('faq.json');
        foreach ($faqs as $f) {
            \App\Models\Faq::updateOrCreate(
                ['question' => $f['question']],
                [
                    'answer' => $f['answer'] ?? '',
                    'status' => $f['status'] ?? 'active',
                    'ord' => $f['order'] ?? $f['ord'] ?? 0,
                ]
            );
        }
    }

    protected function seedBanners(): void
    {
        $banners = $this->loadJson('banners.json');
        foreach ($banners as $b) {
            \App\Models\Banner::updateOrCreate(
                ['title' => $b['title'] ?? ''],
                [
                    'subtitle' => $b['subtitle'] ?? '',
                    'button_text' => $b['buttonText'] ?? $b['button_text'] ?? 'Pesan Sekarang',
                    'button_link' => $b['buttonLink'] ?? $b['button_link'] ?? '#',
                    'background_image' => $b['backgroundImage'] ?? $b['background_image'] ?? '',
                    'status' => $b['status'] ?? 'active',
                    'ord' => $b['order'] ?? $b['ord'] ?? 0,
                ]
            );
        }
    }

    protected function seedGallery(): void
    {
        $gallery = $this->loadJson('gallery.json');
        foreach ($gallery as $g) {
            $images = $g['images'] ?? [$g['image'] ?? null];
            foreach ((array) $images as $img) {
                if (!$img) {
                    continue;
                }
                \App\Models\GalleryItem::updateOrCreate(
                    ['image' => $img],
                    [
                        'caption' => $g['caption'] ?? null,
                        'ord' => $g['order'] ?? $g['ord'] ?? 0,
                    ]
                );
            }
        }
    }

    protected function seedOrders(): void
    {
        $orders = $this->loadJson('orders.json');
        foreach ($orders as $o) {
            $date = $o['date'] ?? now()->format('Y-m-d');
            if (str_contains((string) $date, 'T')) {
                $date = substr((string) $date, 0, 10);
            }
            \App\Models\Order::updateOrCreate(
                ['id' => $o['id'] ?? null],
                [
                    'name' => $o['name'] ?? '',
                    'whatsapp' => $o['whatsapp'] ?? '',
                    'products' => json_encode($o['products'] ?? []),
                    'notes' => $o['notes'] ?? null,
                    'date' => $date,
                    'status' => $o['status'] ?? 'pending',
                ]
            );
        }
    }

    protected function seedSettings(): void
    {
        $settings = $this->loadJson('settings.json');
        foreach ($settings as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            \App\Models\Setting::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        }

        // Default yang pasti ada
        foreach (['site_name', 'site_url', 'phone', 'whatsapp'] as $key) {
            if (!\App\Models\Setting::where('key', $key)->exists()) {
                \App\Models\Setting::create(['key' => $key, 'value' => '']);
            }
        }
    }

    protected function seedSeo(): void
    {
        $seo = $this->loadJson('seo.json');
        \App\Models\Seo::updateOrCreate(
            ['id' => 1],
            [
                'site_name' => $seo['siteName'] ?? $seo['site_name'] ?? 'Pempek Palembang',
                'site_url' => $seo['canonicalUrl'] ?? $seo['site_url'] ?? 'https://pempekpalembang.com',
                'default_title' => $seo['metaTitle'] ?? 'Pempek Palembang — Pempek Asli Palembang, Lezat & Fresh',
                'default_description' => $seo['metaDescription'] ?? 'Pempek asli Palembang dibuat fresh setiap hari dari ikan tenggiri pilihan.',
                'favicon' => $seo['favicon'] ?? '/favicon.ico',
                'meta_title' => $seo['metaTitle'] ?? null,
                'meta_description' => $seo['metaDescription'] ?? null,
                'keywords' => $seo['keywords'] ?? null,
                'og_image' => $seo['ogImage'] ?? null,
                'canonical_url' => $seo['canonicalUrl'] ?? null,
                'robots' => $seo['robots'] ?? 'index, follow',
                'google_verification' => $seo['googleVerification'] ?? '',
                'schema_json_ld' => $seo['schemaJsonLd'] ?? '',
            ]
        );
    }
}