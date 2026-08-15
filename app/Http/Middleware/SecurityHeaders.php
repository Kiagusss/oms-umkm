<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security headers: anti-clickjacking, anti-MIME-sniff, referrer policy.
 * Untuk CSP (Content-Security-Policy) lihat SecurityHeadersWithCsp.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // Anti-clickjacking: iframe hanya dari domain sendiri
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Anti-MIME-sniff: browser tidak boleh tebak tipe konten
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Referrer policy: hanya kirim full URL ke domain sendiri
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions policy: matikan fitur browser yang tidak dipakai
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // HSTS: browser harus pakai HTTPS selama 1 tahun (hanya production)
        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
