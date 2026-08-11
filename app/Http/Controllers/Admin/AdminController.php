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
use App\Services\TransactionService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard(Request $request)
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

        // Hitung total pendapatan berdasarkan filter
        $ordersFiltered = Order::where(function ($q) use ($dateQuery) {
            $dateQuery($q);
        })->get();
        
        $totalRevenue = $ordersFiltered->sum(function ($order) {
            $products = json_decode($order->products, true) ?? [];
            return collect($products)->sum(fn($item) => ($item['price'] ?? 0) * ($item['quantity'] ?? 0));
        });

        // Stats utama
        $stats = [
            'totalProducts' => Product::count(),
            'totalOrders' => Order::count(),
            'totalPageViews' => \App\Models\PageView::count(),
            'totalRevenue' => $totalRevenue,
            
            // Stats berdasarkan filter
            'filteredOrders' => $ordersFiltered->count(),
            'filteredRevenue' => $totalRevenue,
            'filteredPageViews' => \App\Models\PageView::where(function ($q) use ($dateQuery) {
                $dateQuery($q);
            })->count(),
        ];

        // Pesanan terbaru (10 terakhir)
        $recentOrders = Order::with([])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($order) {
                $products = json_decode($order->products, true) ?? [];
                $total = collect($products)->sum(fn($item) => ($item['price'] ?? 0) * ($item['quantity'] ?? 0));
                return [
                    'id' => $order->id,
                    'name' => $order->name,
                    'whatsapp' => $order->whatsapp,
                    'date' => $order->date,
                    'status' => $order->status,
                    'total' => $total,
                    'created_at' => $order->created_at,
                ];
            });

        return view('admin.dashboard', compact('stats', 'recentOrders', 'filter'));
    }

    public function pos()
    {
        $products = Product::where('status', 'active')->orderBy('ord')->get();
        $categories = Category::where('status', 'active')->orderBy('ord')->get();
        $packages = Package::where('status', 'active')->orderBy('ord')->get();

        return view('admin.pos', compact('products', 'categories', 'packages'));
    }

    /**
     * API POS: daftar produk aktif + kategori (untuk fetch Alpine di view pos).
     */
    public function posProducts()
    {
        return response()->json([
            'products' => Product::where('status', 'active')->orderBy('ord')->get(['id', 'name', 'price', 'stock', 'thumbnail', 'category_id']),
            'categories' => Category::where('status', 'active')->orderBy('ord')->get(['id', 'name']),
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