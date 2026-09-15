<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = auth()->user() ?? (session('admin_user_id') ? User::find(session('admin_user_id')) : null);

        if (!$user || !$user->is_active) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Akses ditolak (Unauthorized)'], 403);
            }
            abort(403, 'Akses ditolak.');
        }

        if ($user->isOwner() || $user->hasRole($roles)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['error' => 'Akses ditolak: role tidak sesuai.'], 403);
        }

        abort(403, 'Akses ditolak: role tidak sesuai.');
    }
}
