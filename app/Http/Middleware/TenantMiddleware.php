<?php

namespace App\Http\Middleware;

use App\Models\Store;
use App\Services\Tenant\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // 1. If route has store slug parameter (e.g. /store/{slug})
        if ($storeSlug = $request->route('storeSlug') ?? $request->route('slug')) {
            $store = Store::where('slug', $storeSlug)->first();
            if ($store) {
                TenantContext::setStore($store);
                view()->share('currentStore', $store);
                return $next($request);
            }
        }

        // 2. In Admin dashboard context
        if ($request->is('admin*')) {
            $selectedStoreId = session('selected_store_id');
            $store = null;

            if ($selectedStoreId) {
                $store = Store::find($selectedStoreId);
            }

            // Fallback to user's assigned store or first store or default
            if (!$store && $user) {
                if ($user->store_id) {
                    $store = Store::find($user->store_id);
                } elseif ($user->stores()->exists()) {
                    $store = $user->stores()->first();
                }
            }

            if (!$store) {
                $store = Store::defaultStore();
            }

            if ($store) {
                TenantContext::setStore($store);
                view()->share('currentStore', $store);
            }

            // Provide accessible stores for switcher dropdown
            if ($user) {
                $accessibleStores = $user->isPlatformAdmin()
                    ? Store::all()
                    : $user->stores()->get();

                if ($accessibleStores->isEmpty() && $store) {
                    $accessibleStores = collect([$store]);
                }
                view()->share('userStores', $accessibleStores);
            }
        }

        return $next($request);
    }
}
