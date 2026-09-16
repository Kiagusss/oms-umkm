<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
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

        // Owner has absolute access
        if ($user->isOwner()) {
            return $next($request);
        }

        // Check if user has any of the requested permissions
        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                return $next($request);
            }
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['error' => 'Anda tidak memiliki hak akses untuk tindakan ini.'], 403);
        }

        abort(403, 'Anda tidak memiliki hak akses untuk tindakan ini.');
    }
}
