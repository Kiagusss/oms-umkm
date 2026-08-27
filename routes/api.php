<?php

use App\Http\Controllers\Api\TrackController;
use App\Http\Controllers\Api\UploadImageController;
use App\Http\Controllers\Api\VoucherController;
use App\Http\Controllers\Ai\DiaPempekAIController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — pempek2
|--------------------------------------------------------------------------
| Publik: /api/track, /api/ai/chat (chatbot pelanggan).
| Sisanya (POS, upload, voucher, AI admin) butuh session admin — lihat grup di bawah.
*/

Route::post('/track', [TrackController::class, 'store']);

// Cek ongkir (Biteship) — publik, rate-limited di controller
Route::get('/ongkir/areas', [\App\Http\Controllers\Api\OngkirController::class, 'areas']);
Route::get('/ongkir/rates', [\App\Http\Controllers\Api\OngkirController::class, 'rates']);

// Chatbot publik "Dia Pempek" (rate limit 20/menit via throttle:chat)
Route::post('/ai/chat', [DiaPempekAIController::class, 'publicChat'])->middleware('throttle:chat');

// POS + upload + voucher: khusus admin (session admin_authenticated).
// AdminAuth mengembalikan 401 JSON untuk request API.
Route::middleware('admin.api')->group(function () {
    Route::get('/pos-products', [\App\Http\Controllers\Admin\AdminController::class, 'posProducts']);
    Route::post('/pos-checkout', [\App\Http\Controllers\Admin\AdminController::class, 'posCheckout']);
    Route::post('/upload-image', [UploadImageController::class, 'store']);
    Route::post('/voucher/apply', [VoucherController::class, 'apply']);
});

// Chatbot admin (agent dengan tool baca DB): wajib login admin, PIN tetap sebagai lapis kedua.
Route::post('/ai/admin/chat', [DiaPempekAIController::class, 'adminChat'])
    ->middleware(['admin.api', 'throttle:chat']);
