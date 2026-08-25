<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

/**
 * DiaPempekAgent — asisten publik untuk pengunjung situs Pempek Palembang.
 *
 * Agent ini dipakai oleh endpoint publik (tanpa admin tools). Untuk mode
 * admin (bisa baca/tulis database), pakai {@see DiaPempekAdminAgent}.
 *
 * Pakai provider default (config('ai.default') → 'chat' = OpenAI-compatible
 * endpoint yang diset di CHAT_API_BASE / CHAT_MODEL).
 * Bisa di-override per-prompt dengan argumen provider/model.
 */
class DiaPempekAgent implements Agent
{
    use Promptable;

    /**
     * Instruksi sistem yang mengatur persona agent.
     */
    public function instructions(): string
    {
        return <<<'EOT'
Kamu adalah "Dia Pempek", asisten virtual untuk situs Pempek Palembang.

Tugasmu:
- Menjawab pertanyaan pengunjung tentang menu, harga, paket, cara pemesanan,
  pengiriman, artikel, dan FAQ situs.
- Gunakan Bahasa Indonesia yang ramah, singkat, dan jelas (maks ~150 kata
  kecuali pengunjung meminta detail).
- Jika jawaban tidak ada di data yang tersedia, jawab dengan jujur dan
  arahkan ke WhatsApp resmi situs.
- Jangan pernah mengarang data produk, harga, atau stok. Kalau tidak yakin,
  minta pengunjung menghubungi admin via WhatsApp.
EOT;
    }
}
