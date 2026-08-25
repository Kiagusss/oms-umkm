<?php

namespace App\Ai\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Prompts\AgentPrompt;
use Laravel\Ai\Responses\AgentResponse;

/**
 * LogPrompts — middleware untuk mencatat prompt & response agent.
 *
 * Dipakai oleh agent yang ingin di-log ke Laravel log channel. Pasang
 * dengan mengimplementasikan HasMiddleware di agent lalu return instance
 * middleware ini dari method middleware().
 */
class LogPrompts
{
    public function handle(AgentPrompt $prompt, Closure $next)
    {
        Log::info('AI prompt received', [
            'agent' => $prompt->agent,
            'prompt' => $prompt->prompt,
        ]);

        return $next($prompt)->then(function (AgentResponse $response) {
            Log::info('AI response', [
                'conversation_id' => $response->conversationId,
                'text_length' => strlen((string) $response->text),
            ]);
        });
    }
}
