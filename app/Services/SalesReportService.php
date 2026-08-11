<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class SalesReportService
{
    /**
     * Pesanan yang dihitung sebagai pendapatan: semua kecuali cancelled.
     */
    private function revenueOrders(Collection $orders): Collection
    {
        return $orders->reject(fn (Order $order) => $order->status === 'cancelled');
    }

    /**
     * Net revenue per order: subtotal produk - diskon voucher.
     */
    public function orderRevenue(Order $order): int
    {
        return max(0, $order->total() - (int) ($order->discount ?? 0));
    }

    public function totalRevenue(Collection $orders): int
    {
        return $this->revenueOrders($orders)
            ->sum(fn (Order $order) => $this->orderRevenue($order));
    }

    public function orderCount(Collection $orders): int
    {
        return $this->revenueOrders($orders)->count();
    }

    /**
     * Pendapatan per hari untuk N hari terakhir (termasuk hari ini).
     * Hari tanpa pesanan diisi 0 agar chart selalu kontinu.
     *
     * @return array<int, array{date: string, label: string, orders: int, revenue: int}>
     */
    public function dailyRevenue(Collection $orders, int $days = 7): array
    {
        $revenueOrders = $this->revenueOrders($orders);

        $byDate = $revenueOrders
            ->groupBy(fn (Order $order) => Carbon::parse($order->date)->toDateString())
            ->map(function (Collection $group) {
                return [
                    'orders'  => $group->count(),
                    'revenue' => $group->sum(fn (Order $order) => $this->orderRevenue($order)),
                ];
            });

        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $key = $day->toDateString();
            $data = $byDate->get($key, ['orders' => 0, 'revenue' => 0]);

            $result[] = [
                'date'    => $key,
                'label'   => $day->translatedFormat('D d/m'),
                'orders'  => $data['orders'],
                'revenue' => $data['revenue'],
            ];
        }

        return $result;
    }

    /**
     * Ambil items JSON dari order. Cast 'array' di model tidak konsisten
     * (sqlite text column), jadi decode manual — sama seperti Order::total().
     *
     * @return array<int, array<string, mixed>>
     */
    private function orderItems(Order $order): array
    {
        $products = $order->products;

        if (is_array($products)) {
            return $products;
        }

        if (is_string($products) && $products !== '') {
            return json_decode($products, true) ?? [];
        }

        return [];
    }

    /**
     * Produk terlaris berdasarkan kuantitas, dari JSON items dalam orders.
     *
     * @return Collection<int, array{product_id: int, name: string, quantity: int, revenue: int}>
     */
    public function topProducts(Collection $orders, int $limit = 5): Collection
    {
        $items = $this->revenueOrders($orders)
            ->flatMap(fn (Order $order) => $this->orderItems($order))
            ->filter(fn ($item) => is_array($item) && isset($item['productId'], $item['quantity']));

        return $items
            ->groupBy('productId')
            ->map(function (Collection $group) {
                $first = $group->first();

                return [
                    'product_id' => $first['productId'],
                    'name'       => $first['productName'] ?? 'Produk #'.$first['productId'],
                    'quantity'   => $group->sum('quantity'),
                    'revenue'    => $group->sum(fn ($item) => ($item['price'] ?? 0) * $item['quantity']),
                ];
            })
            ->sortByDesc('quantity')
            ->take($limit)
            ->values();
    }

    /**
     * Breakdown metode pembayaran: jumlah pesanan + pendapatan per metode.
     *
     * @return Collection<int, array{method: string, orders: int, revenue: int}>
     */
    public function paymentMethodBreakdown(Collection $orders): Collection
    {
        return $this->revenueOrders($orders)
            ->groupBy(fn (Order $order) => $order->payment_method ?: 'Tunai')
            ->map(function (Collection $group, string $method) {
                return [
                    'method'  => $method,
                    'orders'  => $group->count(),
                    'revenue' => $group->sum(fn (Order $order) => $this->orderRevenue($order)),
                ];
            })
            ->sortByDesc('revenue')
            ->values();
    }
}
