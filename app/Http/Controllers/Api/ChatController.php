<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\Order;
use App\Models\Package;
use App\Models\Product;
use App\Models\Seo;
use App\Models\Setting;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    private const PUBLIC_SYSTEM = <<<EOT
Kamu adalah asisten virtual "Dia Pempek" untuk situs Pempek Palembang.
Kamu menjawab pertanyaan pengunjung seputar menu, harga, paket, cara pemesanan, pengiriman, artikel, dan FAQ.
Gunakan Bahasa Indonesia yang ramah. Jawab singkat dan jelas (maksimal ~150 kata kecuali diminta detail).
Jika jawaban tidak ada di data situs, katakan jujur bahwa kamu tidak tahu dan arahkan ke WhatsApp resmi.
EOT;

    private const INTERNAL_SYSTEM = <<<EOT
Kamu adalah asisten internal admin situs Pempek Palembang.
Kamu bisa membaca SELURUH data situs (produk, stok, paket, artikel, FAQ, testimoni, banner, galeri, kategori, pesanan, pengaturan, SEO) yang disertakan di bawah.
Tugasmu membantu admin: menganalisis penjualan, mengecek stok, merangkum pesanan, memberi saran produk/harga, dll.
Kamu JUGA bisa mengubah database via tools: membuat pesanan, mengubah status pesanan, mengubah stok produk.
Aturan penggunaan tools:
- Panggil tool hanya jika data yang diperlukan (mis. nama produk) sudah pasti dan admin sudah mengonfirmasi niatnya.
- Sebelum membuat pesanan, pastikan nama produk persis dengan yang ada di data.
- Setelah tool selesai, sampaikan hasilnya ke admin dengan ringkas.
Gunakan Bahasa Indonesia yang jelas dan ringkas.
JANGAN pernah menampilkan data pribadi pelanggan (nama lengkap, alamat, nomor WhatsApp) secara mentah — cukup ringkasan/statistik.
Jika diminta sesuatu di luar data yang tersedia, jawab dengan jujur.
EOT;

    public function chat(Request $request)
    {
        $data = $request->validate([
            'messages' => 'required|array|min:1',
            'messages.*.role' => 'required|in:system,user,assistant,tool',
            'messages.*.content' => 'required|string',
            'admin' => 'sometimes|boolean',
        ]);

        $isAdmin = (bool) ($data['admin'] ?? false);
        $ip = $request->ip();

        // Rate limit: publik 20/menit, admin 60/menit
        $key = 'chat:' . ($isAdmin ? 'admin:' : 'pub:') . $ip;
        $count = (int) Cache::get($key, 0);
        if ($count >= ($isAdmin ? 60 : 20)) {
            return response()->json(['error' => 'Terlalu banyak pesan. Coba lagi nanti.'], 429);
        }
        Cache::put($key, $count + 1, now()->addMinute());

        $pin = config('services.chat.pin');
        $apiKey = config('services.chat.api_key');

        if ($isAdmin && (!$pin || !$apiKey)) {
            return response()->json(['error' => 'Mode admin belum dikonfigurasi (CHAT_ADMIN_PIN / DEEPSEEK_API_KEY belum diset).'], 503);
        }

        $system = self::PUBLIC_SYSTEM;
        if ($isAdmin) {
            $system = self::INTERNAL_SYSTEM . "\n\n--- DATA SITUS SAAT INI ---\n" . $this->buildSiteContext();
        }

        // Tanpa API key → mode demo berbasis data lokal
        if (!$apiKey) {
            $last = end($data['messages'])['content'] ?? '';
            return response()->json([
                'reply' => "[Mode demo — DEEPSEEK_API_KEY belum diset]\n\nPertanyaanmu: \"$last\"\n\nData situs:\n" . substr($this->buildSiteContext(), 0, 1200),
            ]);
        }

        $base = rtrim(config('services.chat.base_url', 'https://rzgwipd.abc-tunnel.us/v1'), '/');
        $model = config('services.chat.model', 'gratis');

        $messages = array_merge([['role' => 'system', 'content' => $system]], array_map(fn ($m) => [
            'role' => $m['role'],
            'content' => $m['content'],
        ], $data['messages']));

        try {
            $resp = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post($base . '/chat/completions', [
                'model' => $model,
                'messages' => $messages,
                'temperature' => 0.4,
                'max_tokens' => 1024,
                'stream' => false,
            ]);

            $json = $resp->json();

            // Gateway tertentu mengembalikan SSE — parse baris data: terakhir
            if (!$json && str_contains((string) $resp->body(), 'data:')) {
                foreach (array_reverse(explode("\n", $resp->body())) as $line) {
                    $line = trim($line);
                    if (!str_starts_with($line, 'data:') || str_ends_with($line, '[DONE]')) continue;
                    $parsed = json_decode(substr($line, 5), true);
                    if ($parsed) { $json = $parsed; break; }
                }
            }

            $reply = $json['choices'][0]['message']['content'] ?? null;
            if (!$reply) {
                return response()->json(['error' => 'Model tidak mengembalikan jawaban.'], 502);
            }

            return response()->json(['reply' => $reply]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Gagal menghubungi model AI: ' . $e->getMessage()], 502);
        }
    }

    /**
     * Verifikasi PIN admin chat. Rate limit 5 percobaan / 10 menit per IP.
     * POST /api/chat/pin { pin: "..." }
     */
    public function verifyPin(Request $request)
    {
        $ip = $request->ip();
        $attempts = (array) Cache::get('chatpin:' . $ip, ['count' => 0, 'reset_at' => 0]);
        if (now()->timestamp > $attempts['reset_at']) {
            $attempts = ['count' => 0, 'reset_at' => now()->addMinutes(10)->timestamp];
        }
        if ($attempts['count'] >= 5) {
            return response()->json(['error' => 'Terlalu banyak percobaan. Coba lagi nanti.'], 429);
        }

        $data = $request->validate(['pin' => 'required|string']);
        $pin = config('services.chat.pin');

        if (!$pin || !hash_equals((string) $pin, (string) $data['pin'])) {
            $attempts['count']++;
            Cache::put('chatpin:' . $ip, $attempts, now()->addMinutes(10));
            return response()->json(['error' => 'PIN salah.'], 403);
        }

        Cache::forget('chatpin:' . $ip);
        return response()->json(['ok' => true]);
    }

    /**
     * Kumpulkan data situs saat ini sebagai teks konteks untuk admin chat.
     */
    private function buildSiteContext(): string
    {
        $parts = [];

        $products = Product::with('category')->get();
        $parts[] = "PRODUK (" . $products->count() . "):";
        foreach ($products as $p) {
            $parts[] = sprintf(
                "- %s | %s | Rp%s | stok: %d | kategori: %s | status: %s%s",
                $p->name,
                $p->short_description ?: '-',
                number_format($p->price),
                $p->stock,
                $p->category?->name ?? '-',
                $p->status,
                $p->is_best_seller ? ' | BEST SELLER' : ''
            );
        }

        $packages = Package::where('status', 'active')->get();
        $parts[] = "\nPAKET (" . $packages->count() . "):";
        foreach ($packages as $pk) {
            $items = is_array($pk->items) ? implode(', ', array_map(fn ($i) => $i['quantity'] . '× ' . $i['name'], $pk->items)) : '';
            $parts[] = "- {$pk->name} | Rp" . number_format($pk->price) . " | {$items}";
        }

        $faqs = Faq::where('status', 'active')->get();
        $parts[] = "\nFAQ (" . $faqs->count() . "):";
        foreach ($faqs as $f) {
            $parts[] = "- Q: {$f->question}\n  A: " . Str::limit($f->answer, 200);
        }

        $categories = Category::where('status', 'active')->get();
        $parts[] = "\nKATEGORI: " . $categories->pluck('name')->implode(', ');

        $articles = Article::where('status', 'published')->latest('date')->take(5)->get();
        $parts[] = "\nARTIKEL TERBARU:";
        foreach ($articles as $a) {
            $parts[] = "- {$a->title} ({$a->date})";
        }

        $testimonials = Testimonial::where('status', 'active')->get();
        $parts[] = "\nTESTIMONI (" . $testimonials->count() . "):";
        foreach ($testimonials->take(3) as $t) {
            $parts[] = "- {$t->name}: \"{$t->comment}\" (" . str_repeat('★', $t->rating) . ")";
        }

        $orders = Order::latest('date')->take(10)->get();
        $parts[] = "\nPESANAN TERBARU (" . $orders->count() . "):";
        $totalRevenue = $orders->sum(function ($o) {
            return collect(json_decode($o->products ?? '[]', true))->sum(fn ($i) => $i['price'] * $i['quantity']);
        });
        $parts[] = "- Total pendapatan (10 pesanan terakhir): Rp" . number_format($totalRevenue);
        foreach ($orders as $o) {
            $parts[] = "- #{$o->id} | {$o->date} | {$o->name} | {$o->status}";
        }

        $settings = Setting::pluck('value', 'key')->toArray();
        $parts[] = "\nPENGATURAN: " . json_encode($settings, JSON_UNESCAPED_UNICODE);

        $seo = Seo::first();
        $parts[] = "SEO: " . ($seo ? $seo->default_title . ' | ' . $seo->default_description : '-');

        return implode("\n", $parts);
    }
}
