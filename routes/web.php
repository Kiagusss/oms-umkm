<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SeoController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\ArticleController as PublicArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PublicCategoryController;
use App\Http\Controllers\PublicProductController;
use Illuminate\Support\Facades\Route;

// ─── Landing ──────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/artikel', [PublicArticleController::class, 'index'])->name('artikel.index');
Route::get('/artikel/{slug}', [PublicArticleController::class, 'show'])->name('artikel.show');
Route::get('/produk/{slug}', [PublicProductController::class, 'show'])->name('produk.show');
Route::get('/kategori/{slug}', [PublicCategoryController::class, 'show'])->name('kategori.show');

// ─── Auth admin ───────────────────────────────────────────
Route::prefix('admin')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'login'])->name('admin.login.post');
    Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');

    Route::middleware('admin.auth')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('pos', [AdminController::class, 'pos'])->name('admin.pos');
        Route::resource('produk', ProductController::class)->names('admin.produk');
        Route::get('artikel/{artikel}/preview', [ArticleController::class, 'preview'])->name('admin.artikel.preview');
        Route::resource('paket', PackageController::class)->names('admin.paket');
        Route::resource('kategori', CategoryController::class)->names('admin.kategori');
        Route::resource('artikel', ArticleController::class)->names('admin.artikel');
        Route::resource('testimoni', TestimonialController::class)->names('admin.testimoni');
        Route::resource('banner', BannerController::class)->names('admin.banner');
        Route::resource('faq', FaqController::class)->names('admin.faq');
        Route::resource('galeri', GalleryController::class)->names('admin.galeri');
        Route::resource('pesanan', OrderController::class)->names('admin.pesanan');
        Route::get('pengaturan', [SettingController::class, 'edit'])->name('admin.pengaturan');
        Route::put('pengaturan', [SettingController::class, 'update'])->name('admin.pengaturan.update');
        Route::get('seo', [SeoController::class, 'edit'])->name('admin.seo');
        Route::put('seo', [SeoController::class, 'update'])->name('admin.seo.update');
    });
});
