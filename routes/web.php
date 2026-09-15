<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\FinancialReportController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\RecipeController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\SeoController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StockTransferController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\ArticleController as PublicArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PublicCategoryController;
use App\Http\Controllers\PublicOrderController;
use App\Http\Controllers\PublicProductController;
use Illuminate\Support\Facades\Route;

// ─── Landing ──────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/artikel', [PublicArticleController::class, 'index'])->name('artikel.index');
Route::get('/artikel/{slug}', [PublicArticleController::class, 'show'])->name('artikel.show');
Route::get('/produk/{slug}', [PublicProductController::class, 'show'])->name('produk.show');
Route::post('/produk/{slug}/review', [PublicProductController::class, 'storeReview'])->name('produk.review')->middleware('throttle:3,1');
Route::get('/kategori/{slug}', [PublicCategoryController::class, 'show'])->name('kategori.show');

// Checkout publik + status pesanan
Route::get('/checkout', [PublicOrderController::class, 'form'])->name('checkout.form');
Route::post('/checkout', [PublicOrderController::class, 'checkout'])->name('checkout')->middleware('throttle:6,1');
Route::post('/api/public/qris/settle', [PublicOrderController::class, 'settleQris'])->middleware('throttle:10,1');
Route::get('/pesanan/{order}', [PublicOrderController::class, 'show'])->name('pesanan.show');

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
        Route::get('pesanan/{pesanan}/struk', [OrderController::class, 'struk'])->name('admin.pesanan.struk');
        Route::resource('voucher', VoucherController::class)->names('admin.voucher');
        Route::get('review', [ReviewController::class, 'index'])->name('admin.review.index');
        Route::patch('review/{review}/approve', [ReviewController::class, 'approve'])->name('admin.review.approve');
        Route::delete('review/{review}', [ReviewController::class, 'destroy'])->name('admin.review.destroy');
        Route::get('pengaturan', [SettingController::class, 'edit'])->name('admin.pengaturan');
        Route::put('pengaturan', [SettingController::class, 'update'])->name('admin.pengaturan.update');
        Route::get('seo', [SeoController::class, 'edit'])->name('admin.seo');
        Route::put('seo', [SeoController::class, 'update'])->name('admin.seo.update');

        // Branch Switcher
        Route::post('switch-branch', [AuthController::class, 'switchBranch'])->name('admin.switch-branch');

        // Multi-Branch Management
        Route::resource('cabang', BranchController::class)->names('admin.cabang')->middleware('permission:branch.manage');

        // Inventory & Stock Ledger
        Route::get('inventori/mutasi', [InventoryController::class, 'movements'])->name('admin.inventori.mutasi')->middleware('permission:inventory.manage');
        Route::post('inventori/{inventori}/adjust', [InventoryController::class, 'adjust'])->name('admin.inventori.adjust')->middleware('permission:inventory.manage');
        Route::resource('inventori', InventoryController::class)->names('admin.inventori')->middleware('permission:inventory.manage');

        // Recipes / BOM & HPP History
        Route::post('resep/{resep}/recalculate', [RecipeController::class, 'recalculate'])->name('admin.resep.recalculate')->middleware('permission:recipe.manage');
        Route::resource('resep', RecipeController::class)->names('admin.resep')->middleware('permission:recipe.manage');

        // Supplier & Purchases
        Route::resource('supplier', SupplierController::class)->names('admin.supplier')->middleware('permission:purchase.manage');
        Route::post('pembelian/{pembelian}/receive', [PurchaseController::class, 'receive'])->name('admin.pembelian.receive')->middleware('permission:purchase.manage');
        Route::post('pembelian/{pembelian}/cancel', [PurchaseController::class, 'cancel'])->name('admin.pembelian.cancel')->middleware('permission:purchase.manage');
        Route::resource('pembelian', PurchaseController::class)->names('admin.pembelian')->middleware('permission:purchase.manage');

        // Expenses / Biaya Operasional
        Route::resource('biaya', ExpenseController::class)->names('admin.biaya')->middleware('permission:expense.manage');

        // Inter-Branch Stock Transfers
        Route::post('transfer/{transfer}/ship', [StockTransferController::class, 'ship'])->name('admin.transfer.ship')->middleware('permission:transfer.manage');
        Route::post('transfer/{transfer}/receive', [StockTransferController::class, 'receive'])->name('admin.transfer.receive')->middleware('permission:transfer.manage');
        Route::post('transfer/{transfer}/cancel', [StockTransferController::class, 'cancel'])->name('admin.transfer.cancel')->middleware('permission:transfer.manage');
        Route::resource('transfer', StockTransferController::class)->names('admin.transfer')->middleware('permission:transfer.manage');

        // Staff & Role Management
        Route::resource('pengguna', UserController::class)->names('admin.pengguna')->middleware('permission:user.manage');

        // Financial Reports & Analytics
        Route::get('laporan/keuangan', [FinancialReportController::class, 'index'])->name('admin.laporan.keuangan')->middleware('permission:report.view');
        Route::get('laporan/keuangan/export', [FinancialReportController::class, 'exportCsv'])->name('admin.laporan.keuangan.export')->middleware('permission:report.view');

        // AI assistant (Laravel AI SDK) — hanya untuk admin login.
        // Form/UI pengelolaan ada di resources/views/admin/ai/.
        Route::view('ai', 'admin.ai.index')->name('admin.ai');
        Route::post('ai/chat', [\App\Http\Controllers\Ai\DiaPempekAIController::class, 'adminChat'])
            ->name('admin.ai.chat');
    });
});
