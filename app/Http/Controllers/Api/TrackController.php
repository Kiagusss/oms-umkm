<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrackController extends Controller
{
    /**
     * Catat page view (path, ip, user agent). Idempoten per (path, ip) per hari.
     * POST /api/track  { path: "/", referrer?: "..." }
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'path' => 'required|string|max:255',
            'referrer' => 'nullable|string|max:500',
        ]);

        $ip = $request->ip();
        $today = now()->toDateString();

        // Mencegah duplikat dari halaman yang sama di hari yang sama
        $exists = DB::table('page_views')
            ->where('path', $data['path'])
            ->where('ip', $ip)
            ->whereDate('created_at', $today)
            ->exists();

        if (!$exists) {
            DB::table('page_views')->insert([
                'path' => $data['path'],
                'ip' => $ip,
                'user_agent' => substr($request->userAgent() ?? '', 0, 255),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json(['ok' => true]);
    }
}
