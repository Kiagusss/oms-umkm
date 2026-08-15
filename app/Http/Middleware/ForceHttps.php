<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirect HTTP → HTTPS dengan status 308 (preserve HTTP method).
 *
 * PENTING: Pakai 308, BUKAN 301.
 * - 301: browser ubah POST jadi GET setelah redirect → endpoint POST-only balas 405.
 * - 308: HTTP method preserve → POST tetap POST, aman untuk form submission.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/308
 */
class ForceHttps
{
    public function handle(Request $request, Closure $next): Response
    {
        // Kalau sudah HTTPS (atau environment non-production), lewati.
        if (! $this->shouldForce($request)) {
            return $next($request);
        }

        // 308 = Permanent Redirect + preserve method (vs 301 yang mengubah jadi GET)
        return redirect()->secure($request->getRequestUri(), 308);
    }

    private function shouldForce(Request $request): bool
    {
        // Jangan force di local/testing/CLI
        if (app()->environment(['local', 'testing'])) {
            return false;
        }

        // Sudah secure (https / loopback / trusted proxy)
        if ($request->isSecure()) {
            return false;
        }

        // Hanya force di production
        return app()->environment('production');
    }
}
