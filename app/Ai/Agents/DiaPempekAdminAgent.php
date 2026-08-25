<?php

namespace App\Ai\Agents;

use App\Ai\Tools\ListProductsTool;
use App\Ai\Tools\GetOrderSummaryTool;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;

/**
 * DiaPempekAdminAgent — asisten internal untuk admin situs.
 *
 * Tidak seperti {@see DiaPempekAgent} (mode publik), agent ini punya akses
 * ke tools baca-data (produk, ringkasan pesanan). Untuk operasi tulis
 * (create/update/delete) akan ditambahkan di tool terpisah.
 */
#[MaxSteps(8)]
#[Timeout(60)]
class DiaPempekAdminAgent implements Agent, HasTools
{
    use Promptable;

    public function instructions(): string
    {
        return <<<'EOT'
Kamu adalah asisten internal admin untuk situs Pempek Palembang.

Tugasmu:
- Membantu admin menganalisis penjualan, mengecek stok produk,
  merangkum pesanan, dan menjawab pertanyaan operasional.
- Kamu punya akses ke tools baca-data. Panggil tool hanya jika memang
  diperlukan untuk menjawab pertanyaan admin.
- Jika data yang diminta tidak tersedia lewat tool, jawab dengan jujur.
- Gunakan Bahasa Indonesia yang jelas dan ringkas.
- JANGAN pernah menampilkan data pribadi pelanggan (nama lengkap, alamat,
  nomor WhatsApp) secara mentah. Cukup ringkasan/statistik.
EOT;
    }

    /**
     * Tools yang tersedia untuk agent ini.
     *
     * @return array<int, \Laravel\Ai\Contracts\Tool>
     */
    public function tools(): iterable
    {
        return [
            new ListProductsTool,
            new GetOrderSummaryTool,
        ];
    }
}
