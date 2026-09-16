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

        // Fallback for existing admin_authenticated session where admin_user_id was not populated
        if (!$user && session('admin_authenticated')) {
            $adminEmail = config('app.admin_email');
            if ($adminEmail) {
                $configAdmin = User::where('email', $adminEmail)->first();
                if ($configAdmin && $configAdmin->is_active) {
                    $user = $configAdmin;
                    auth()->login($user);
                    session(['admin_user_id' => $user->id]);
                }
            }
        }

        if (!$user || !$user->is_active) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Akses ditolak (Unauthorized)'], 403);
            }
            abort(403, 'Akses ditolak.');
        }

        if ($user->isOwner() || $user->hasRole(...$roles)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['error' => 'Akses ditolak: role tidak sesuai.'], 403);
        }

        abort(403, 'Akses ditolak: role tidak sesuai.');
    }
}
