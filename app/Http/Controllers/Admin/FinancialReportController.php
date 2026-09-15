<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Expense;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinancialReportController extends Controller
{
    public function index(Request $request)
    {
        $selectedBranchId = session('selected_branch_id');
        $branchId = $request->get('branch_id') ?: $selectedBranchId;

        $range = $request->get('range', '30');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        if (!$startDate || !$endDate) {
            $endDate = now()->toDateString();
            $startDate = match ($range) {
                'today' => now()->toDateString(),
                '7'     => now()->subDays(6)->toDateString(),
                'month' => now()->startOfMonth()->toDateString(),
                default => now()->subDays(29)->toDateString(),
            };
        }

        // Orders query
        $ordersQuery = Order::where('status', '!=', 'cancelled')
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate);

        if ($branchId) {
            $ordersQuery->where('branch_id', $branchId);
        }

        $orders = $ordersQuery->get();

        // Expenses query
        $expensesQuery = Expense::whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate);

        if ($branchId) {
            $expensesQuery->where('branch_id', $branchId);
        }

        $expenses = $expensesQuery->get();

        // Key Financial Metrics
        $grossSales = 0;
        $totalDiscount = 0;
        $totalCogs = 0;

        foreach ($orders as $order) {
            $grossSales += (int) $order->total();
            $totalDiscount += (int) ($order->discount ?? 0);
            $totalCogs += (int) ($order->total_cogs ?? 0);
        }

        $netSales = max(0, $grossSales - $totalDiscount);
        $grossProfit = $netSales - $totalCogs;
        $grossMarginPct = $netSales > 0 ? round(($grossProfit / $netSales) * 100, 1) : 0;

        $totalExpenses = (int) $expenses->sum('amount');
        $netProfit = $grossProfit - $totalExpenses;
        $netMarginPct = $netSales > 0 ? round(($netProfit / $netSales) * 100, 1) : 0;

        // Product Profitability Breakdown
        $productStats = [];
        foreach ($orders as $order) {
            $items = is_array($order->products) ? $order->products : json_decode((string) $order->products, true) ?? [];
            foreach ($items as $item) {
                $pid = $item['productId'] ?? null;
                $vid = $item['variantId'] ?? null;
                if (!$pid) continue;

                $key = $vid ? "{$pid}_{$vid}" : "{$pid}_base";
                $name = $item['productName'] ?? "Produk #{$pid}";
                if (!empty($item['variantName'])) {
                    $name .= " ({$item['variantName']})";
                }

                $qty = (int) ($item['quantity'] ?? 1);
                $price = (int) ($item['price'] ?? 0);
                $cogs = (int) ($item['cogs'] ?? 0);

                if (!isset($productStats[$key])) {
                    $productStats[$key] = [
                        'name'         => $name,
                        'quantity'     => 0,
                        'revenue'      => 0,
                        'cogs'         => 0,
                        'gross_profit' => 0,
                    ];
                }

                $rev = $price * $qty;
                $cost = $cogs * $qty;
                $productStats[$key]['quantity'] += $qty;
                $productStats[$key]['revenue'] += $rev;
                $productStats[$key]['cogs'] += $cost;
                $productStats[$key]['gross_profit'] += ($rev - $cost);
            }
        }

        // Calculate margin % for each product
        foreach ($productStats as $k => $stat) {
            $productStats[$k]['margin_pct'] = $stat['revenue'] > 0
                ? round(($stat['gross_profit'] / $stat['revenue']) * 100, 1)
                : 0;
        }

        uasort($productStats, fn($a, $b) => $b['gross_profit'] <=> $a['gross_profit']);

        // Expense by category breakdown
        $expensesByCategory = $expenses->groupBy('category')->map(function ($group, $cat) {
            return [
                'category' => $cat,
                'total'    => $group->sum('amount'),
                'count'    => $group->count(),
            ];
        })->sortByDesc('total');

        $branches = Branch::where('status', 'active')->get();

        return view('admin.laporan.keuangan', [
            'startDate'          => $startDate,
            'endDate'            => $endDate,
            'range'              => $range,
            'branchId'           => $branchId,
            'branches'           => $branches,
            'grossSales'         => $grossSales,
            'totalDiscount'      => $totalDiscount,
            'netSales'           => $netSales,
            'totalCogs'          => $totalCogs,
            'grossProfit'        => $grossProfit,
            'grossMarginPct'     => $grossMarginPct,
            'totalExpenses'      => $totalExpenses,
            'netProfit'          => $netProfit,
            'netMarginPct'       => $netMarginPct,
            'orderCount'         => $orders->count(),
            'productStats'       => array_values($productStats),
            'expensesByCategory' => $expensesByCategory,
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $selectedBranchId = session('selected_branch_id');
        $branchId = $request->get('branch_id') ?: $selectedBranchId;
        $startDate = $request->get('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        $ordersQuery = Order::where('status', '!=', 'cancelled')
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->with('branch');

        if ($branchId) {
            $ordersQuery->where('branch_id', $branchId);
        }

        $orders = $ordersQuery->latest('date')->get();

        $filename = "laporan-keuangan-{$startDate}-to-{$endDate}.csv";

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID Pesanan', 'Tanggal', 'Cabang', 'Pelanggan', 'Metode Bayar', 'Total Kotor', 'Diskon', 'Pendapatan Bersih', 'Total HPP', 'Laba Kotor', 'Status']);

            foreach ($orders as $order) {
                $subtotal = $order->total();
                $discount = (int) ($order->discount ?? 0);
                $net = max(0, $subtotal - $discount);
                $cogs = (int) ($order->total_cogs ?? 0);
                $profit = $net - $cogs;

                fputcsv($handle, [
                    $order->id,
                    $order->date,
                    $order->branch?->name ?? 'Cabang Utama',
                    $order->name,
                    $order->payment_method,
                    $subtotal,
                    $discount,
                    $net,
                    $cogs,
                    $profit,
                    $order->status,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
