@extends('layouts.app')

@section('title', 'Daftar Toko & Mitra UMKM — Marketplace Kuliner Nusantara')
@section('description', 'Temukan toko kuliner dan UMKM terpercaya dengan produk autentik, lezat, dan berkualitas.')

@section('content')
<div class="bg-[var(--color-paper)] min-h-screen pt-24 pb-16">
    <div class="mx-auto max-w-7xl px-5 sm:px-8">
        {{-- Header --}}
        <div class="mb-10 text-center sm:text-left">
            <span class="inline-block rounded-full bg-[var(--color-accent-light)] px-3 py-1 text-xs font-semibold text-[var(--color-accent)] mb-3">
                Direktori UMKM
            </span>
            <h1 class="text-3xl font-bold tracking-tight text-[var(--color-ink)] sm:text-4xl">
                Jelajahi Mitra & Toko UMKM
            </h1>
            <p class="mt-2 text-sm text-[var(--color-ink-2)] max-w-2xl">
                Dukung produk UMKM lokal nusantara dengan memesan langsung dari toko resmi mitra terverifikasi kami.
            </p>
        </div>

        {{-- Filter & Search Bar --}}
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-[var(--color-paper-3)] mb-8">
            <form method="GET" action="{{ route('marketplace.stores') }}" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Cari nama toko, produk, atau kota..."
                        class="w-full rounded-xl border border-[var(--color-paper-3)] bg-[var(--color-paper)] px-4 py-2.5 pl-10 text-sm focus:border-[var(--color-accent)] focus:outline-none focus:ring-1 focus:ring-[var(--color-accent)]"
                    >
                    <svg class="absolute left-3.5 top-3 text-[var(--color-ink-3)]" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>

                @if($cities->isNotEmpty())
                <select
                    name="city"
                    class="rounded-xl border border-[var(--color-paper-3)] bg-[var(--color-paper)] px-4 py-2.5 text-sm focus:border-[var(--color-accent)] focus:outline-none focus:ring-1 focus:ring-[var(--color-accent)]"
                >
                    <option value="">Semua Kota</option>
                    @foreach($cities as $city)
                    <option value="{{ $city }}" {{ request('city') === $city ? 'selected' : '' }}>{{ $city }}</option>
                    @endforeach
                </select>
                @endif

                <button type="submit" class="rounded-xl bg-[var(--color-accent)] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[var(--color-accent-hover)] transition-colors">
                    Filter Toko
                </button>
                @if(request()->hasAny(['q', 'city']))
                <a href="{{ route('marketplace.stores') }}" class="rounded-xl bg-[var(--color-paper-2)] px-4 py-2.5 text-sm font-medium text-[var(--color-ink-2)] hover:bg-[var(--color-paper-3)] transition-colors text-center">
                    Reset
                </a>
                @endif
            </form>
        </div>

        {{-- Stores Grid --}}
        @if($stores->isEmpty())
        <div class="text-center py-16 bg-white rounded-2xl border border-[var(--color-paper-3)]">
            <svg class="mx-auto text-[var(--color-ink-3)] mb-4" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            <h3 class="text-lg font-bold text-[var(--color-ink)]">Toko Tidak Ditemukan</h3>
            <p class="text-sm text-[var(--color-ink-2)] mt-1">Coba kata kunci lain atau hapus filter.</p>
        </div>
        @else
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($stores as $store)
            <div class="group relative flex flex-col justify-between overflow-hidden rounded-2xl bg-white border border-[var(--color-paper-3)] p-6 shadow-sm transition-all hover:shadow-md hover:border-[var(--color-accent)]">
                <div>
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[var(--color-accent-light)] text-[var(--color-accent)] font-bold text-lg">
                                {{ strtoupper(substr($store->name, 0, 2)) }}
                            </div>
                            <div>
                                <h2 class="font-bold text-[var(--color-ink)] group-hover:text-[var(--color-accent)] transition-colors">
                                    {{ $store->name }}
                                </h2>
                                <p class="text-xs text-[var(--color-ink-3)] flex items-center gap-1 mt-0.5">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"></path>
                                        <circle cx="12" cy="9" r="2.5"></circle>
                                    </svg>
                                    {{ $store->city ?? 'Indonesia' }}
                                </p>
                            </div>
                        </div>
                        @if($store->is_featured)
                        <span class="rounded-full bg-amber-50 px-2.5 py-0.5 text-[10px] font-semibold text-amber-700 border border-amber-200">
                            ★ Unggulan
                        </span>
                        @endif
                    </div>

                    <p class="text-sm text-[var(--color-ink-2)] line-clamp-2 mb-4">
                        {{ $store->description ?? 'Toko kuliner nusantara berkualitas fresh setiap hari.' }}
                    </p>
                </div>

                <div class="pt-4 border-t border-[var(--color-paper-2)] flex items-center justify-between">
                    <span class="text-xs font-medium text-[var(--color-ink-3)]">
                        <strong>{{ $store->products_count }}</strong> Produk Tersedia
                    </span>
                    <a
                        href="{{ route('marketplace.storefront', $store->slug) }}"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-[var(--color-paper-2)] px-4 py-2 text-xs font-semibold text-[var(--color-ink)] transition-colors group-hover:bg-[var(--color-accent)] group-hover:text-white"
                    >
                        Kunjungi Toko
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-10">
            {{ $stores->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
