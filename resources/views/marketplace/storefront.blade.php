@extends('layouts.app')

@section('title', $store->name . ' — Storefront Toko Resmi')
@section('description', $store->description ?? 'Belanja produk resmi dari ' . $store->name . ' dengan jaminan kualitas terbaik.')

@section('content')
<div class="bg-[var(--color-paper)] min-h-screen pt-20 pb-16">
    {{-- Store Header Banner --}}
    <div class="bg-gradient-to-r from-stone-900 to-stone-800 text-white py-12 px-5 sm:px-8 border-b border-stone-700">
        <div class="mx-auto max-w-7xl flex flex-col md:flex-row items-center md:items-start justify-between gap-6">
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5 text-center sm:text-left">
                <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-[var(--color-accent)] text-white text-3xl font-extrabold shadow-lg">
                    {{ strtoupper(substr($store->name, 0, 2)) }}
                </div>
                <div>
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">{{ $store->name }}</h1>
                        @if($store->is_featured)
                        <span class="rounded-full bg-amber-400/20 px-2.5 py-0.5 text-xs font-semibold text-amber-300 border border-amber-400/30">
                            ★ Unggulan
                        </span>
                        @endif
                    </div>
                    <p class="mt-2 text-sm text-stone-300 max-w-xl">
                        {{ $store->description ?? 'Toko mitra resmi terverifikasi di Platform UMKM Nusantara.' }}
                    </p>
                    <div class="mt-3 flex flex-wrap items-center justify-center sm:justify-start gap-4 text-xs text-stone-300">
                        @if($store->city)
                        <span class="flex items-center gap-1">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"></path>
                                <circle cx="12" cy="9" r="2.5"></circle>
                            </svg>
                            {{ $store->city }}
                        </span>
                        @endif
                        @if($store->phone)
                        <span class="flex items-center gap-1">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                            {{ $store->phone }}
                        </span>
                        @endif
                        <span class="rounded-md bg-white/10 px-2 py-0.5 font-medium">
                            {{ $products->total() }} Produk
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('marketplace.stores') }}" class="rounded-xl border border-stone-600 bg-white/5 px-4 py-2 text-xs font-semibold text-white hover:bg-white/10 transition-colors">
                    ← Direktori Toko
                </a>
            </div>
        </div>
    </div>

    {{-- Content Container --}}
    <div class="mx-auto max-w-7xl px-5 sm:px-8 mt-8">
        {{-- Store Search & Category Filter --}}
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            {{-- Category Pills --}}
            <div class="flex flex-wrap items-center gap-2">
                <a
                    href="{{ route('marketplace.storefront', ['slug' => $store->slug, 'q' => request('q')]) }}"
                    class="rounded-xl px-3.5 py-1.5 text-xs font-semibold transition-colors {{ !request('category') ? 'bg-[var(--color-accent)] text-white' : 'bg-white text-[var(--color-ink-2)] border border-[var(--color-paper-3)] hover:bg-[var(--color-paper-2)]' }}"
                >
                    Semua Produk
                </a>
                @foreach($categories as $cat)
                <a
                    href="{{ route('marketplace.storefront', ['slug' => $store->slug, 'category' => $cat->slug, 'q' => request('q')]) }}"
                    class="rounded-xl px-3.5 py-1.5 text-xs font-semibold transition-colors {{ request('category') === $cat->slug ? 'bg-[var(--color-accent)] text-white' : 'bg-white text-[var(--color-ink-2)] border border-[var(--color-paper-3)] hover:bg-[var(--color-paper-2)]' }}"
                >
                    {{ $cat->name }}
                </a>
                @endforeach
            </div>

            {{-- Search Bar --}}
            <form method="GET" action="{{ route('marketplace.storefront', $store->slug) }}" class="relative min-w-[260px]">
                @if(request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Cari produk di toko ini..."
                    class="w-full rounded-xl border border-[var(--color-paper-3)] bg-white px-3.5 py-2 pl-9 text-xs focus:border-[var(--color-accent)] focus:outline-none focus:ring-1 focus:ring-[var(--color-accent)]"
                >
                <svg class="absolute left-3 top-2.5 text-[var(--color-ink-3)]" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </form>
        </div>

        {{-- Products Grid --}}
        @if($products->isEmpty())
        <div class="text-center py-16 bg-white rounded-2xl border border-[var(--color-paper-3)]">
            <svg class="mx-auto text-[var(--color-ink-3)] mb-4" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="8" y1="15" x2="16" y2="15"></line>
                <line x1="9" y1="9" x2="9.01" y2="9"></line>
                <line x1="15" y1="9" x2="15.01" y2="9"></line>
            </svg>
            <h3 class="text-lg font-bold text-[var(--color-ink)]">Belum Ada Produk</h3>
            <p class="text-sm text-[var(--color-ink-2)] mt-1">Tidak ada produk yang cocok dengan pencarian Anda di toko ini.</p>
        </div>
        @else
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 sm:gap-6">
            @foreach($products as $prod)
            <div class="group flex flex-col justify-between overflow-hidden rounded-2xl bg-white border border-[var(--color-paper-3)] shadow-sm transition-all hover:shadow-md">
                <div>
                    {{-- Image Container --}}
                    <div class="relative aspect-square w-full overflow-hidden bg-[var(--color-paper-2)]">
                        @if($prod->image)
                        <img
                            src="{{ asset($prod->image) }}"
                            alt="{{ $prod->name }}"
                            class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                            loading="lazy"
                        >
                        @else
                        <div class="flex h-full w-full items-center justify-center text-[var(--color-ink-3)] text-xs">
                            Tidak Ada Gambar
                        </div>
                        @endif

                        @if($prod->is_featured)
                        <span class="absolute top-2.5 left-2.5 rounded-full bg-[var(--color-accent)] px-2 py-0.5 text-[10px] font-bold text-white shadow">
                            Unggulan
                        </span>
                        @endif
                    </div>

                    {{-- Product Info --}}
                    <div class="p-4">
                        @if($prod->category)
                        <span class="text-[11px] font-medium text-[var(--color-accent)]">
                            {{ $prod->category->name }}
                        </span>
                        @endif
                        <h3 class="font-bold text-sm text-[var(--color-ink)] line-clamp-1 mt-0.5">
                            <a href="{{ route('marketplace.product', ['storeSlug' => $store->slug, 'productSlug' => $prod->slug]) }}" class="hover:underline">
                                {{ $prod->name }}
                            </a>
                        </h3>
                        <p class="text-xs text-[var(--color-ink-2)] line-clamp-2 mt-1">
                            {{ $prod->description ?? 'Deskripsi produk fresh dan higienis.' }}
                        </p>
                    </div>
                </div>

                {{-- Price and Action --}}
                <div class="p-4 pt-0">
                    <div class="flex items-baseline justify-between mb-3">
                        <span class="text-sm sm:text-base font-extrabold text-[var(--color-ink)]">
                            Rp {{ number_format($prod->price, 0, ',', '.') }}
                        </span>
                        <span class="text-[11px] text-[var(--color-ink-3)]">
                            Stok {{ $prod->stock }}
                        </span>
                    </div>

                    <button
                        type="button"
                        @click="$store.cart.addItem({
                            id: {{ $prod->id }},
                            name: '{{ addslashes($prod->name) }}',
                            price: {{ $prod->price }},
                            image: '{{ $prod->image ? asset($prod->image) : '' }}',
                            stock: {{ $prod->stock }},
                            store_id: {{ $store->id }},
                            store_name: '{{ addslashes($store->name) }}'
                        })"
                        class="w-full rounded-xl bg-[var(--color-accent)] py-2 text-center text-xs font-semibold text-white transition-colors hover:bg-[var(--color-accent-hover)]"
                    >
                        + Tambah Keranjang
                    </button>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-10">
            {{ $products->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
