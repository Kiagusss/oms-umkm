@extends('admin.layouts.app')

@section('title', 'Laporan Laba Rugi & HPP — Admin Pempek')

@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Laporan Keuangan & Laba Rugi (P&L)</h1>
        <p class="mt-1 text-sm text-slate-500">Analisis komprehensif pendapatan kotor, HPP riil, beban operasional, margin laba bersih, dan profitabilitas produk.</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.laporan.keuangan.export', request()->query()) }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-slate-800">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export Laporan (CSV)
        </a>
    </div>
</div>

{{-- Filter Bar --}}
<div class="mb-8 rounded-2xl border border-slate-100 bg-white p-5 shadow-xs">
    <form method="GET" action="{{ route('admin.laporan.keuangan') }}" class="flex flex-wrap items-end gap-4">
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">Dari Tanggal</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-mono">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">Sampai Tanggal</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-mono">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">Filter Cabang</label>
            <select name="branch_id" class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-medium">
                <option value="">Semua Cabang Konsolidasi</option>
                @foreach($branches as $b)
                    <option value="{{ $b->id }}" @selected($branchId == $b->id)>{{ $b->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="rounded-xl bg-emerald-700 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-800">Terapkan Filter</button>
    </form>
</div>

{{-- Top Financial KPIs --}}
<div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-xs">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Penjualan Bersih (Net Sales)</p>
        <p class="mt-2 text-2xl font-black text-slate-900 font-mono">Rp {{ number_format($netSales, 0, ',', '.') }}</p>
        <p class="mt-1 text-xs text-slate-400">Bruto: Rp {{ number_format($grossSales, 0, ',', '.') }} | Diskon: Rp {{ number_format($totalDiscount, 0, ',', '.') }}</p>
    </div>

    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-xs">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Beban Pokok Penjualan (HPP)</p>
        <p class="mt-2 text-2xl font-black text-amber-700 font-mono">Rp {{ number_format($totalCogs, 0, ',', '.') }}</p>
        <p class="mt-1 text-xs text-slate-400">Total modal bahan resep & produk</p>
    </div>

    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-xs">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Laba Kotor (Gross Profit)</p>
        <p class="mt-2 text-2xl font-black text-emerald-700 font-mono">Rp {{ number_format($grossProfit, 0, ',', '.') }}</p>
        <div class="mt-1 flex items-center gap-1.5">
            <span class="inline-block rounded-md bg-emerald-50 px-1.5 py-0.5 text-xs font-bold text-emerald-700">{{ $grossMarginPct }}%</span>
            <span class="text-xs text-slate-400">Gross Margin</span>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-xs">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Laba Bersih Usaha (Net Profit)</p>
        <p class="mt-2 text-2xl font-black {{ $netProfit >= 0 ? 'text-emerald-800' : 'text-rose-600' }} font-mono">Rp {{ number_format($netProfit, 0, ',', '.') }}</p>
        <div class="mt-1 flex items-center gap-1.5">
            <span class="inline-block rounded-md {{ $netProfit >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }} px-1.5 py-0.5 text-xs font-bold">{{ $netMarginPct }}%</span>
            <span class="text-xs text-slate-400">Net Profit Margin</span>
        </div>
    </div>
</div>

{{-- P&L Income Statement Breakdown Table --}}
<div class="grid gap-6 lg:grid-cols-3 mb-8">
    <div class="lg:col-span-2 rounded-2xl border border-slate-100 bg-white p-6 shadow-xs">
        <h3 class="text-base font-bold text-slate-800 mb-4">Laporan Laba Rugi Formal (Income Statement)</h3>
        <div class="divide-y divide-slate-100 text-sm">
            <div class="py-3 flex justify-between items-center">
                <span class="font-medium text-slate-700">Pendapatan Penjualan Kotor (Gross Sales)</span>
                <span class="font-mono font-semibold text-slate-900">Rp {{ number_format($grossSales, 0, ',', '.') }}</span>
            </div>
            <div class="py-3 flex justify-between items-center text-slate-500">
                <span class="pl-4">Dikurangi: Potongan & Voucher Diskon</span>
                <span class="font-mono text-rose-600">(Rp {{ number_format($totalDiscount, 0, ',', '.') }})</span>
            </div>
            <div class="py-3 flex justify-between items-center bg-slate-50/50 px-3 font-semibold rounded-lg">
                <span class="text-slate-800">Pendapatan Bersih (Net Revenue)</span>
                <span class="font-mono text-slate-900">Rp {{ number_format($netSales, 0, ',', '.') }}</span>
            </div>
            <div class="py-3 flex justify-between items-center text-slate-500">
                <span class="pl-4">Beban Pokok Penjualan (HPP / Biaya Bahan Resep)</span>
                <span class="font-mono text-rose-600">(Rp {{ number_format($totalCogs, 0, ',', '.') }})</span>
            </div>
            <div class="py-3 flex justify-between items-center bg-emerald-50/50 px-3 font-bold text-emerald-900 rounded-lg">
                <span>LABA KOTOR (GROSS PROFIT)</span>
                <span class="font-mono text-base">Rp {{ number_format($grossProfit, 0, ',', '.') }} ({{ $grossMarginPct }}%)</span>
            </div>
            <div class="py-3 flex justify-between items-center text-slate-500">
                <span class="pl-4">Total Beban Operasional & Overhead (OPEX)</span>
                <span class="font-mono text-rose-600">(Rp {{ number_format($totalExpenses, 0, ',', '.') }})</span>
            </div>
            <div class="py-4 flex justify-between items-center bg-slate-900 px-4 font-black text-white rounded-xl">
                <span class="tracking-wide">LABA BERSIH BERJALAN (NET PROFIT)</span>
                <span class="font-mono text-lg {{ $netProfit >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                    Rp {{ number_format($netProfit, 0, ',', '.') }} ({{ $netMarginPct }}%)
                </span>
            </div>
        </div>
    </div>

    {{-- Expense Breakdown by Category --}}
    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-xs">
        <h3 class="text-base font-bold text-slate-800 mb-4">Rincian Beban Operasional</h3>
        <div class="space-y-3">
            @forelse($expensesByCategory as $cat => $val)
                <div>
                    <div class="flex justify-between text-xs font-semibold mb-1">
                        <span class="capitalize text-slate-600">{{ str_replace('_', ' ', $cat) }}</span>
                        <span class="font-mono text-slate-900">Rp {{ number_format($val['total'], 0, ',', '.') }}</span>
                    </div>
                    @php
                        $expPct = $totalExpenses > 0 ? round(($val['total'] / $totalExpenses) * 100, 1) : 0;
                    @endphp
                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                        <div class="bg-rose-500 h-full rounded-full" style="width: {{ $expPct }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-xs text-slate-400 text-center py-8">Belum ada beban operasional pada periode ini.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- Product Profitability Matrix --}}
<div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-xs">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-base font-bold text-slate-800">Matriks Profitabilitas Menu & Produk</h3>
            <p class="text-xs text-slate-500">Margin laba kotor riil per item menu berdasarkan HPP resep saat transaksi terjadi.</p>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left text-xs">
            <thead>
                <tr class="border-b border-slate-100 font-semibold text-slate-500 uppercase">
                    <th class="py-3 px-4">Nama Produk / Menu</th>
                    <th class="py-3 px-4 text-center">Porsi Terjual</th>
                    <th class="py-3 px-4 text-right">Total Omset</th>
                    <th class="py-3 px-4 text-right">Total HPP Modal</th>
                    <th class="py-3 px-4 text-right">Laba Kotor</th>
                    <th class="py-3 px-4 text-right">Margin %</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @forelse($productStats as $row)
                    <tr>
                        <td class="py-3 px-4 font-semibold text-slate-900">{{ $row['name'] }}</td>
                        <td class="py-3 px-4 text-center font-mono font-bold">{{ $row['quantity'] }}</td>
                        <td class="py-3 px-4 text-right font-mono">Rp {{ number_format($row['revenue'], 0, ',', '.') }}</td>
                        <td class="py-3 px-4 text-right font-mono text-slate-500">Rp {{ number_format($row['cogs'], 0, ',', '.') }}</td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-emerald-700">Rp {{ number_format($row['gross_profit'], 0, ',', '.') }}</td>
                        <td class="py-3 px-4 text-right font-mono font-bold">
                            <span class="rounded-full px-2 py-0.5 {{ $row['margin_pct'] >= 50 ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                {{ $row['margin_pct'] }}%
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-6 text-center text-slate-400">Belum ada transaksi menu pada rentang waktu ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
