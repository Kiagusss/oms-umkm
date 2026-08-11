<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PosCheckoutRequest;
use App\Models\Article;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\Order;
use App\Models\Package;
use App\Models\Product;
use App\Models\Testimonial;
use App\Services\SalesReportService;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard(Request $request, SalesReportService $report)
    {
        // Filter periode: today, month, year (default: today)
        $filter = $request->get('filter', 'today');

        // Base query untuk filter tanggal
        $dateQuery = function ($query) use ($filter) {
            if ($filter === 'today') {
                $query->whereDate('created_at', today());
            } elseif ($filter === 'month') {
                $query->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
            } elseif ($filter === 'year') {
                $query->whereYear('created_at', now()->year);
            }
        };

        // Orders dalam periode filter (termasuk cancelled untuk totalOrders)
        $ordersFiltered = Order::where(function ($q) use ($dateQuery) {
            $dateQuery($q);
        })->get();

        // Pendapatan: subtotal - diskon voucher, pesanan cancelled tidak dihitung
        $totalRevenue    = $report->totalRevenue($ordersFiltered);
        $filteredOrders  = $report->orderCount($ordersFiltered);
        $filteredRevenue = $totalRevenue;
        $totalDiscount   = $ordersFiltered
            ->reject(fn ($order) => $order->status === 'cancelled')
            ->sum(fn ($order) => (int) ($order->discount ?? 0));

        // Stats utama
        $stats = [
            'totalProducts' => Product::count(),
            'totalOrders' => Order::count(),
            'totalPageViews' => \App\Models\PageView::count(),
            'totalRevenue' => $totalRevenue,

            // Stats berdasarkan filter
            'filteredOrders' => $filteredOrders,
            'filteredRevenue' => $filteredRevenue,
            'filteredDiscount' => $totalDiscount,
            'filteredPageViews' => \App\Models\PageView::where(function ($q) use ($dateQuery) {
                $dateQuery($q);
            })->count(),
        ];

        // Pesanan terbaru (10 terakhir)
        $recentOrders = Order::with([])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($order) use ($report) {
                return [
                    'id' => $order->id,
                    'name' => $order->name,
                    'whatsapp' => $order->whatsapp,
                    'date' => $order->date,
                    'status' => $order->status,
                    'total' => $report->orderRevenue($order),
                    'created_at' => $order->created_at,
                ];
            });

        // Laporan penjualan: tren 7 hari + produk terlaris + metode pembayaran
        $dailyRevenue   = $report->dailyRevenue($ordersFiltered, 7);
        $topProducts    = $report->topProducts($ordersFiltered, 5);
        $paymentMethods = $report->paymentMethodBreakdown($ordersFiltered);

        return view('admin.dashboard', compact(
            'stats', 'recentOrders', 'filter',
            'dailyRevenue', 'topProducts', 'paymentMethods'
        ));
    }

    public function pos()
    {
        $products = Product::where('status', 'active')->orderBy('ord')->get();
        $categories = Category::where('status', 'active')->orderBy('ord')->get();
        $packages = Package::where('status', 'active')->orderBy('ord')->get();

        $happyHour = app(\App\Services\HappyHourService::class);

        return view('admin.pos', compact('products', 'categories', 'packages'))
            ->with('happyHourActive', $happyHour->isActive())
            ->with('happyHourPercent', $happyHour->discountPercent())
            ->with('happyHourDiscounts', $products->mapWithKeys(fn ($p) => [
                $p->id => $happyHour->discountedPrice((int) $p->price, $happyHour->discountPercent()),
            ]));
    }

    /**
     * API POS: daftar produk aktif + kategori (untuk fetch Alpine di view pos).
     */
    public function posProducts()
    {
        $happyHour = app(\App\Services\HappyHourService::class);
        $percent = $happyHour->discountPercent();
        $active = $percent > 0;

        $products = Product::where('status', 'active')->orderBy('ord')->get(['id', 'name', 'price', 'stock', 'thumbnail', 'category_id'])
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'price' => (int) $p->price,
                'discounted_price' => $happyHour->discountedPrice((int) $p->price, $percent),
                'stock' => $p->stock,
                'thumbnail' => $p->thumbnail,
                'category_id' => $p->category_id,
            ]);

        return response()->json([
            'products' => $products,
            'categories' => Category::where('status', 'active')->orderBy('ord')->get(['id', 'name']),
            'happy_hour_active' => $active,
            'happy_hour_percent' => $percent,
        ]);
    }

    /**
     * API POS: checkout — server-side price/stock/tax calc + DB transaction.
     */
    public function posCheckout(PosCheckoutRequest $request, TransactionService $service)
    {
        try {
            $order = $service->checkout($request->validated());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        $discount = (int) ($order->discount ?? 0);

        return response()->json([
            'ok'            => true,
            'order_id'      => $order->id,
            'subtotal'      => $order->total(),
            'total'         => $order->total() - $discount,
            'payment_method'=> $order->payment_method,
            'cash_received' => $order->cash_received,
            'change_amount' => $order->change_amount,
            'discount'      => $discount,
        ]);
    }
}