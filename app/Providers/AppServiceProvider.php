<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Variabel global untuk semua view: nomor WA + link order
        View::share('waNumber', config('app.wa_number', '6281234567890'));
        View::share('waLink', 'https://wa.me/' . config('app.wa_number', '6281234567890') . '?text=' . urlencode('Halo, saya ingin memesan Pempek Palembang.'));

        // Rate limiter untuk endpoint chat AI (20 req/menit per IP)
        RateLimiter::for('chat', fn (Request $request) => Limit::perMinute(20)->by($request->ip()));
    }
}
