@extends('layouts.app')

@section('title', $product->name . ' — ' . $store->name)
@section('description', Str::limit(strip_tags($product->description ?? 'Beli ' . $product->name . ' di ' . $store->name), 150))

@section('content')
<div class="bg-[var(--color-paper)] min-h-screen pt-24 pb-16">
    <div class="mx-auto max-w-7xl px-5 sm:px-8">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-xs text-[var(--color-ink-3)] mb-6 overflow-x-auto whitespace-nowrap">
            <a href="{{ url('/') }}" class="hover:text-[var(--color-ink)]">Beranda</a>
            <span>/</span>
            <a href="{{ route('marketplace.stores') }}" class="hover:text-[var(--color-ink)]">Toko UMKM</a>
            <span>/</span>
            <a href="{{ route('marketplace.storefront', $store->slug) }}" class="hover:text-[var(--color-ink)]">{{ $store->name }}</a>
            <span>/</span>
            <span class="text-[var(--color-ink)] font-semibold">{{ $product->name }}</span>
        </nav>

        {{-- Product Main Card --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12 bg-white rounded-3xl p-6 sm:p-10 border border-[var(--color-paper-3)] shadow-sm">
            {{-- Product Image Gallery --}}
            <div class="space-y-4">
                <div class="aspect-square w-full rounded-2xl overflow-hidden bg-[var(--color-paper-2)] border border-[var(--color-paper-3)]">
                    @if($product->image)
                    <img src="{{ asset($product->image) }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                    @else
                    <div class="flex h-full w-full items-center justify-center text-[var(--color-ink-3)] text-sm">
                        Tidak Ada Gambar
                    </div>
                    @endif
                </div>

                @if(!empty($product->gallery) && is_array($product->gallery))
                <div class="flex gap-3 overflow-x-auto pb-2">
                    @foreach($product->gallery as $img)
                    <img src="{{ asset($img) }}" alt="{{ $product->name }}" class="h-16 w-16 rounded-xl object-cover border border-[var(--color-paper-3)]">
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Product Info & Action --}}
            <div class="flex flex-col justify-between" x-data="{ qty: 1 }">
                <div>
                    {{-- Store Badge --}}
                    <div class="flex items-center gap-3 mb-4 p-3 rounded-2xl bg-[var(--color-paper)] border border-[var(--color-paper-3)]">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--color-accent-light)] text-[var(--color-accent)] font-bold text-sm">
                            {{ strtoupper(substr($store->name, 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-[var(--color-ink-3)]">Dijual oleh</p>
                            <a href="{{ route('marketplace.storefront', $store->slug) }}" class="font-bold text-sm text-[var(--color-ink)] hover:text-[var(--color-accent)] truncate block">
                                {{ $store->name }}
                            </a>
                        </div>
                        <a href="{{ route('marketplace.storefront', $store->slug) }}" class="text-xs font-semibold text-[var(--color-accent)] hover:underline whitespace-nowrap">
                            Kunjungi Toko →
                        </a>
                    </div>

                    @if($product->category)
                    <span class="inline-block text-xs font-semibold text-[var(--color-accent)] uppercase tracking-wider mb-2">
                        {{ $product->category->name }}
                    </span>
                    @endif

                    <h1 class="text-2xl sm:text-3xl font-extrabold text-[var(--color-ink)] tracking-tight">
                        {{ $product->name }}
                    </h1>

                    <div class="mt-4 flex items-baseline gap-4">
                        <span class="text-2xl sm:text-3xl font-black text-[var(--color-accent)]">
                            Rp {{ number_format($product->price, 0, ',', '.') }}
                        </span>
                        <span class="text-xs text-[var(--color-ink-3)] font-medium">
                            Stok tersedia: {{ $product->stock }} unit
                        </span>
                    </div>

                    <div class="mt-6 border-t border-[var(--color-paper-2)] pt-6">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-[var(--color-ink-3)] mb-2">Deskripsi Produk</h2>
                        <p class="text-sm text-[var(--color-ink-2)] leading-relaxed whitespace-pre-line">
                            {{ $product->description ?? 'Produk kuliner higienis dan fresh dari UMKM terpercaya.' }}
                        </p>
                    </div>

                    @if(!empty($product->variants) && is_array($product->variants))
                    <div class="mt-6 border-t border-[var(--color-paper-2)] pt-6">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-[var(--color-ink-3)] mb-3">Pilihan Varian</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach($product->variants as $variant)
                            <span class="rounded-xl border border-[var(--color-paper-3)] bg-[var(--color-paper)] px-3 py-1.5 text-xs font-medium text-[var(--color-ink)]">
                                {{ is_array($variant) ? ($variant['name'] ?? json_encode($variant)) : $variant }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Add to Cart Form --}}
                <div class="mt-8 border-t border-[var(--color-paper-2)] pt-6">
                    <div class="flex items-center gap-4 mb-4">
                        <span class="text-xs font-medium text-[var(--color-ink-2)]">Jumlah:</span>
                        <div class="flex items-center rounded-xl border border-[var(--color-paper-3)] bg-[var(--color-paper)]">
                            <button
                                type="button"
                                @click="qty = Math.max(1, qty - 1)"
                                class="px-3 py-1.5 text-sm font-bold text-[var(--color-ink-2)] hover:text-[var(--color-ink)]"
                            >-</button>
                            <span class="w-10 text-center text-sm font-semibold" x-text="qty"></span>
                            <button
                                type="button"
                                @click="qty = Math.min({{ $product->stock }}, qty + 1)"
                                class="px-3 py-1.5 text-sm font-bold text-[var(--color-ink-2)] hover:text-[var(--color-ink)]"
                            >+</button>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="$store.cart.addItem({
                            id: {{ $product->id }},
                            name: '{{ addslashes($product->name) }}',
                            price: {{ $product->price }},
                            image: '{{ $product->image ? asset($product->image) : '' }}',
                            stock: {{ $product->stock }},
                            store_id: {{ $store->id }},
                            store_name: '{{ addslashes($store->name) }}'
                        }, qty)"
                        class="w-full rounded-2xl bg-[var(--color-accent)] py-3.5 text-center text-sm font-bold text-white shadow-md transition-colors hover:bg-[var(--color-accent-hover)]"
                    >
                        + Tambah ke Keranjang
                    </button>
                </div>
            </div>
        </div>

        {{-- Related Products from Same Store --}}
        @if($relatedProducts->isNotEmpty())
        <div class="mt-14">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-[var(--color-ink)]">Produk Lain dari {{ $store->name }}</h2>
                <a href="{{ route('marketplace.storefront', $store->slug) }}" class="text-xs font-semibold text-[var(--color-accent)] hover:underline">
                    Lihat Semua ({{ $store->products()->count() }}) →
                </a>
            </div>

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                @foreach($relatedProducts as $rel)
                <div class="group flex flex-col justify-between overflow-hidden rounded-2xl bg-white border border-[var(--color-paper-3)] p-3 shadow-sm transition-all hover:shadow-md">
                    <div>
                        <div class="aspect-square w-full rounded-xl overflow-hidden bg-[var(--color-paper-2)] mb-2">
                            @if($rel->image)
                            <img src="{{ asset($rel->image) }}" alt="{{ $rel->name }}" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                            @endif
                        </div>
                        <h3 class="text-xs font-bold text-[var(--color-ink)] line-clamp-1">
                            <a href="{{ route('marketplace.product', ['storeSlug' => $store->slug, 'productSlug' => $rel->slug]) }}">
                                {{ $rel->name }}
                            </a>
                        </h3>
                        <p class="text-xs font-black text-[var(--color-accent)] mt-1">
                            Rp {{ number_format($rel->price, 0, ',', '.') }}
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="$store.cart.addItem({
                            id: {{ $rel->id }},
                            name: '{{ addslashes($rel->name) }}',
                            price: {{ $rel->price }},
                            image: '{{ $rel->image ? asset($rel->image) : '' }}',
                            stock: {{ $rel->stock }},
                            store_id: {{ $store->id }},
                            store_name: '{{ addslashes($store->name) }}'
                        })"
                        class="mt-3 w-full rounded-lg bg-[var(--color-paper-2)] py-1.5 text-center text-[11px] font-semibold text-[var(--color-ink)] hover:bg-[var(--color-accent)] hover:text-white transition-colors"
                    >
                        + Keranjang
                    </button>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
