@extends('admin.layouts.app')

@section('title', 'Dashboard — Admin Pempek')

@section('content')
<!-- Header Row -->
<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between animate-fade-up">
    <div>
        <h1 class="text-3xl font-extrabold tracking-tight text-[var(--color-ink)]">Dashboard</h1>
        <p class="mt-1 text-sm font-medium text-[var(--color-ink-3)]">Ringkasan data toko pempek Anda.</p>
    </div>
    
    <!-- Filter Periode -->
    <div class="flex items-center gap-3">
        <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
            <select name="filter" onchange="this.form.submit()" class="rounded-[var(--radius-lg)] border border-[var(--color-paper-3)] bg-white px-4 py-2.5 text-sm font-semibold text-[var(--color-ink-2)]">
                <option value="today" @selected($filter === 'today')>Hari Ini</option>
                <option value="month" @selected($filter === 'month')>Bulan Ini</option>
                <option value="year" @selected($filter === 'year')>Tahun Ini</option>
            </select>
        </form>
    </div>
</div>

<!-- Top row cards (Grid of 4) -->
<div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4 animate-fade-up delay-2">
    <!-- Card 1: Total Produk -->
    <div class="group relative flex flex-col justify-between rounded-[24px] bg-[var(--color-accent)] p-6 text-white shadow-sm hover:shadow-md transition duration-300">
        <div class="flex items-start justify-between">
            <span class="text-sm font-semibold text-emerald-100/90">Total Produk</span>
            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-white text-[var(--color-accent)] transition-transform group-hover:scale-105">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <path d="M21 8l-9-5-9 5 9 5 9-5zm-9 5v9m9-14v9M3 5v9"/>
                </svg>
            </div>
        </div>
        <div class="mt-5">
            <p class="text-4xl font-extrabold tracking-tight">{{ $stats['totalProducts'] }}</p>
            <div class="mt-4 flex items-center gap-1.5 text-xs text-emerald-200 font-bold">
                <span>Produk aktif di katalog</span>
            </div>
        </div>
    </div>

    <!-- Card 2: Total Pesanan -->
    <div class="group relative flex flex-col justify-between rounded-[24px] border border-[var(--color-paper-3)] bg-white p-6 shadow-sm hover:shadow-md transition duration-300">
        <div class="flex items-start justify-between">
            <span class="text-sm font-semibold text-[var(--color-ink-3)]">Total Pesanan</span>
            <div class="flex h-9 w-9 items-center justify-center rounded-full border border-[var(--color-paper-3)] text-[var(--color-ink)] transition-transform group-hover:scale-105">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <path d="M6 3h12l2 18H4L6 3zm4 8h4"/>
                </svg>
            </div>
        </div>
        <div class="mt-5">
            <p class="text-4xl font-extrabold tracking-tight text-[var(--color-ink)]">{{ $stats['filteredOrders'] }}</p>
            <div class="mt-4 flex items-center gap-1.5 text-xs text-[var(--color-ink-3)] font-bold">
                <span class="rounded-md bg-emerald-50 px-2 py-0.5 text-[10px] text-emerald-700 font-extrabold font-mono">{{ $stats['totalOrders'] }}</span>
                <span>Total keseluruhan</span>
            </div>
        </div>
    </div>

    <!-- Card 3: Pengunjung Website -->
    <div class="group relative flex flex-col justify-between rounded-[24px] border border-[var(--color-paper-3)] bg-white p-6 shadow-sm hover:shadow-md transition duration-300">
        <div class="flex items-start justify-between">
            <span class="text-sm font-semibold text-[var(--color-ink-3)]">Pengunjung Website</span>
            <div class="flex h-9 w-9 items-center justify-center rounded-full border border-[var(--color-paper-3)] text-[var(--color-ink)] transition-transform group-hover:scale-105">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 00-3-3.87m-4-12a4 4 0 010 7.75"/>
                </svg>
            </div>
        </div>
        <div class="mt-5">
            <p class="text-4xl font-extrabold tracking-tight text-[var(--color-ink)]">{{ $stats['filteredPageViews'] }}</p>
            <div class="mt-4 flex items-center gap-1.5 text-xs text-[var(--color-ink-3)] font-bold">
                <span class="rounded-md bg-emerald-50 px-2 py-0.5 text-[10px] text-emerald-700 font-extrabold font-mono">{{ $stats['totalPageViews'] }}</span>
                <span>Total keseluruhan</span>
            </div>
        </div>
    </div>

    <!-- Card 4: Total Pendapatan -->
    <div class="group relative flex flex-col justify-between rounded-[24px] border border-[var(--color-paper-3)] bg-white p-6 shadow-sm hover:shadow-md transition duration-300">
        <div class="flex items-start justify-between">
            <span class="text-sm font-semibold text-[var(--color-ink-3)]">Total Pendapatan</span>
            <div class="flex h-9 w-9 items-center justify-center rounded-full border border-[var(--color-paper-3)] text-[var(--color-ink)] transition-transform group-hover:scale-105">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
                </svg>
            </div>
        </div>
        <div class="mt-5">
            <p class="text-4xl font-extrabold tracking-tight text-[var(--color-ink)]">Rp{{ number_format($stats['filteredRevenue']) }}</p>
            <div class="mt-4 text-xs font-bold text-[var(--color-ink-3)] leading-normal">
                Dari {{ $stats['filteredOrders'] }} pesanan
                @if(($stats['filteredDiscount'] ?? 0) > 0)
                    <span class="mt-1 block rounded-md bg-rose-50 px-2 py-0.5 text-[10px] text-rose-600 font-extrabold">
                        Diskon: -Rp{{ number_format($stats['filteredDiscount']) }}
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Laporan Penjualan: Tren 7 Hari + Produk Terlaris + Metode Pembayaran -->
<div class="mt-8 grid gap-6 lg:grid-cols-3 animate-fade-up">
    <!-- Tren Pendapatan 7 Hari -->
    <div class="rounded-[24px] border border-[var(--color-paper-3)] bg-white p-6 shadow-sm lg:col-span-2">
        <div class="mb-6">
            <h2 class="text-xl font-bold text-[var(--color-ink)]">Tren Penjualan 7 Hari</h2>
            <p class="mt-1 text-sm text-[var(--color-ink-3)]">Pendapatan per hari pada periode terpilih</p>
        </div>
        <div class="flex items-end gap-3">
            @php $maxRevenue = max(1, collect($dailyRevenue)->max('revenue')); @endphp
            @foreach($dailyRevenue as $day)
                <div class="flex flex-1 flex-col items-center gap-2">
                    <div class="relative flex w-full items-end justify-center" style="height: 160px;">
                        <div class="group relative w-full max-w-[36px] rounded-t-lg bg-[var(--color-accent)] transition-all duration-300 hover:bg-[var(--color-accent-2)]"
                             style="height: {{ max(4, round($day['revenue'] / $maxRevenue * 100)) }}%">
                            <div class="pointer-events-none absolute -top-9 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-md bg-[var(--color-ink)] px-2 py-1 text-[10px] font-bold text-white opacity-0 transition group-hover:opacity-100">
                                Rp{{ number_format($day['revenue']) }}
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <p class="text-[11px] font-bold text-[var(--color-ink-2)]">{{ $day['label'] }}</p>
                        <p class="text-[10px] font-semibold text-[var(--color-ink-3)]">{{ $day['orders'] }} pesanan</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Produk Terlaris -->
    <div class="rounded-[24px] border border-[var(--color-paper-3)] bg-white p-6 shadow-sm">
        <div class="mb-6">
            <h2 class="text-xl font-bold text-[var(--color-ink)]">Produk Terlaris</h2>
            <p class="mt-1 text-sm text-[var(--color-ink-3)]">Top 5 berdasarkan jumlah terjual</p>
        </div>
        <ul class="space-y-4">
            @forelse($topProducts as $index => $item)
                <li class="flex items-center gap-3">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[var(--color-paper-2)] text-xs font-extrabold text-[var(--color-ink-2)]">
                        {{ $index + 1 }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-[var(--color-ink)]">{{ $item['name'] }}</p>
                        <p class="text-xs text-[var(--color-ink-3)]">{{ $item['quantity'] }} terjual · Rp{{ number_format($item['revenue']) }}</p>
                    </div>
                </li>
            @empty
                <li class="py-6 text-center text-sm text-[var(--color-ink-3)]">Belum ada penjualan.</li>
            @endforelse
        </ul>
    </div>
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-3 animate-fade-up">
    <!-- Metode Pembayaran -->
    <div class="rounded-[24px] border border-[var(--color-paper-3)] bg-white p-6 shadow-sm">
        <div class="mb-6">
            <h2 class="text-xl font-bold text-[var(--color-ink)]">Metode Pembayaran</h2>
            <p class="mt-1 text-sm text-[var(--color-ink-3)]">Distribusi pesanan per metode</p>
        </div>
        <ul class="space-y-3">
            @forelse($paymentMethods as $method)
                <li class="flex items-center justify-between rounded-[var(--radius-lg)] bg-[var(--color-paper-2)] px-4 py-3">
                    <div>
                        <p class="text-sm font-bold text-[var(--color-ink)]">{{ $method['method'] }}</p>
                        <p class="text-xs text-[var(--color-ink-3)]">{{ $method['orders'] }} pesanan</p>
                    </div>
                    <p class="text-sm font-extrabold tabular-nums text-[var(--color-accent)]">Rp{{ number_format($method['revenue']) }}</p>
                </li>
            @empty
                <li class="py-6 text-center text-sm text-[var(--color-ink-3)]">Belum ada data.</li>
            @endforelse
        </ul>
    </div>
</div>

<!-- Pesanan Terbaru -->
<div class="mt-8 animate-fade-up">
    <div class="rounded-[24px] border border-[var(--color-paper-3)] bg-white p-6 shadow-sm">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-[var(--color-ink)]">Pesanan Terbaru</h2>
                <p class="mt-1 text-sm text-[var(--color-ink-3)]">10 pesanan terakhir dari pelanggan</p>
            </div>
            <a href="{{ route('admin.pesanan.index') }}" class="rounded-[var(--radius-lg)] border border-[var(--color-paper-3)] bg-white px-4 py-2 text-sm font-semibold text-[var(--color-ink-2)] transition hover:bg-[var(--color-paper-2)]">
                Lihat Semua
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left text-sm">
                <thead>
                    <tr class="border-b border-[var(--color-paper-3)] bg-[var(--color-paper-2)] font-semibold text-[var(--color-ink)]">
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Pelanggan</th>
                        <th class="px-4 py-3">WhatsApp</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Total</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--color-paper-3)] text-[var(--color-ink-2)]">
                    @forelse($recentOrders as $order)
                        <tr class="hover:bg-[var(--color-paper-2)] transition-colors">
                            <td class="px-4 py-3 font-semibold text-[var(--color-ink)]">#{{ $order['id'] }}</td>
                            <td class="px-4 py-3 font-semibold text-[var(--color-ink)]">{{ $order['name'] }}</td>
                            <td class="px-4 py-3 text-xs text-[var(--color-ink-3)]">{{ $order['whatsapp'] ?: '-' }}</td>
                            <td class="px-4 py-3 text-sm">{{ \Carbon\Carbon::parse($order['date'])->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-3 font-bold tabular-nums text-[var(--color-accent)]">
                                Rp{{ number_format($order['total']) }}
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $statusClasses = [
                                        'pending' => 'bg-amber-50 text-amber-700',
                                        'processing' => 'bg-blue-50 text-blue-700',
                                        'completed' => 'bg-emerald-50 text-emerald-700',
                                        'cancelled' => 'bg-red-50 text-red-700',
                                    ];
                                    $statusLabels = [
                                        'pending' => 'Pending',
                                        'processing' => 'Diproses',
                                        'completed' => 'Selesai',
                                        'cancelled' => 'Dibatalkan',
                                    ];
                                @endphp
                                <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $statusClasses[$order['status']] ?? 'bg-gray-50 text-gray-700' }}">
                                    {{ $statusLabels[$order['status']] ?? ucfirst($order['status']) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.pesanan.show', $order['id']) }}" class="rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-1.5 text-xs font-medium text-[var(--color-accent)] hover:bg-[var(--color-accent-light)] transition">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-sm text-[var(--color-ink-3)]">
                                Belum ada pesanan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
