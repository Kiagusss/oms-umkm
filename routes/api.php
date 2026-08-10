<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\TrackController;
use App\Http\Controllers\Api\UploadImageController;

/*
|--------------------------------------------------------------------------
| API Routes — pempek2
|--------------------------------------------------------------------------
| Semua route /api/* tanpa middleware auth (publik).
*/

Route::post('/track', [TrackController::class, 'store']);

Route::post('/upload-image', [UploadImageController::class, 'store']);

Route::post('/chat', [ChatController::class, 'chat'])->middleware('throttle:chat');
Route::post('/chat/pin', [ChatController::class, 'verifyPin']);

// POS (admin): produk + kategori aktif, dan checkout (membuat order + kurangi stok)
Route::get('/pos-products', [\App\Http\Controllers\Admin\AdminController::class, 'posProducts']);
Route::post('/pos-checkout', [\App\Http\Controllers\Admin\AdminController::class, 'posCheckout']);
