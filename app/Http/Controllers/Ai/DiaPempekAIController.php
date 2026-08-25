<?php

namespace App\Http\Controllers\Ai;

use App\Ai\Agents\DiaPempekAdminAgent;
use App\Ai\Agents\DiaPempekAgent;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * DiaPempekAIController — endpoint HTTP untuk AI SDK.
 *
 * Dua mode:
 *   - public  → POST /api/ai/chat      (rate limit 20/menit, agent publik)
 *   - admin   → POST /admin/ai/chat    (perlu admin auth + PIN, agent admin)
 *
 * Response JSON: { reply, conversation_id, usage? }
 *
 * Endpoint ini ADALAH wrapper tipis di atas AI SDK. Logic bisnis tetap
 * di Agent + Tool. ChatController lama TETAP ADA untuk backward compat
 * (lihat App\Http\Controllers\Api\ChatController).
 */
class DiaPempekAIController extends Controller
{
    public function publicChat(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message' => 'required|string|max:2000',
            'conversation_id' => 'sometimes|nullable|string|max:64',
        ]);

        $ip = $request->ip();
        $key = 'ai:pub:' . $ip;
        $hits = (int) Cache::get($key, 0);
        if ($hits >= 20) {
            return response()->json(['error' => 'Terlalu banyak pesan. Coba lagi nanti.'], 429);
        }
        Cache::put($key, $hits + 1, now()->addMinute());

        try {
            $agent = (new DiaPempekAgent);
            $prompt = $this->prompt($agent, $data);

            $response = $prompt->prompt(
                $data['message'],
                provider: 'chat',
            );

            return response()->json([
                'reply' => (string) $response,
                'conversation_id' => $response->conversationId,
            ]);
        } catch (\Throwable $e) {
            Log::error('AI public chat error', [
                'error' => $e->getMessage(),
                'ip' => $ip,
            ]);
            return response()->json([
                'error' => 'Maaf, asisten AI sedang tidak tersedia. Silakan coba lagi atau hubungi WhatsApp kami.',
            ], 502);
        }
    }

    public function adminChat(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message' => 'required|string|max:4000',
            'conversation_id' => 'sometimes|nullable|string|max:64',
            'pin' => 'sometimes|nullable|string',
        ]);

        // PIN check (sama dengan ChatController)
        $expectedPin = config('services.chat.pin');
        if ($expectedPin && ($data['pin'] ?? null) !== $expectedPin) {
            return response()->json(['error' => 'PIN admin salah.'], 403);
        }

        $ip = $request->ip();
        $key = 'ai:admin:' . $ip;
        $hits = (int) Cache::get($key, 0);
        if ($hits >= 60) {
            return response()->json(['error' => 'Terlalu banyak pesan. Coba lagi nanti.'], 429);
        }
        Cache::put($key, $hits + 1, now()->addMinute());

        try {
            $agent = (new DiaPempekAdminAgent);
            $prompt = $this->prompt($agent, $data);

            $response = $prompt->prompt(
                $data['message'],
                provider: 'chat',
            );

            return response()->json([
                'reply' => (string) $response,
                'conversation_id' => $response->conversationId,
            ]);
        } catch (\Throwable $e) {
            Log::error('AI admin chat error', [
                'error' => $e->getMessage(),
                'ip' => $ip,
            ]);
            return response()->json([
                'error' => 'Maaf, asisten AI admin sedang tidak tersedia.',
            ], 502);
        }
    }

    /**
     * Terapkan conversation_id ke agent (continue mode), atau return
     * instance baru jika tidak ada.
     */
    private function prompt($agent, array $data)
    {
        if (! empty($data['conversation_id'])) {
            return $agent->continue($data['conversation_id']);
        }
        return $agent;
    }
}
