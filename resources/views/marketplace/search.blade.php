@extends('layouts.app')

@section('title', 'Cari Produk UMKM — Marketplace')
@section('description', 'Cari dan temukan beragam produk autentik kuliner dan kerajinan dari mitra UMKM di seluruh Indonesia.')

@section('content')
<div class="bg-[var(--color-paper)] min-h-screen pt-24 pb-16">
    <div class="mx-auto max-w-7xl px-5 sm:px-8">
        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[var(--color-ink)]">
                Pencarian Produk Marketplace
            </h1>
            <p class="text-sm text-[var(--color-ink-2)] mt-1">
                Temukan aneka pilihan produk dari seluruh toko dan mitra UMKM.
            </p>
        </div>

        {{-- Filters & Search Bar --}}
        <div class="bg-white rounded-2xl p-5 border border-[var(--color-paper-3)] shadow-sm mb-8">
            <form method="GET" action="{{ route('marketplace.search') }}" class="space-y-4">
                {{-- Keyword bar --}}
                <div class="relative">
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Ketik nama produk, pempek, cuka, varian..."
                        class="w-full rounded-xl border border-[var(--color-paper-3)] bg-[var(--color-paper)] px-4 py-2.5 pl-10 text-sm focus:border-[var(--color-accent)] focus:outline-none focus:ring-1 focus:ring-[var(--color-accent)]"
                    >
                    <svg class="absolute left-3.5 top-3 text-[var(--color-ink-3)]" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>

                {{-- Filter row --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    {{-- Store filter --}}
                    <div>
                        <label class="block text-xs font-semibold text-[var(--color-ink-3)] mb-1">Pilih Toko</label>
                        <select name="store" class="w-full rounded-xl border border-[var(--color-paper-3)] bg-[var(--color-paper)] px-3 py-2 text-xs focus:border-[var(--color-accent)] focus:outline-none">
                            <option value="">Semua Toko</option>
                            @foreach($stores as $st)
                            <option value="{{ $st->slug }}" {{ request('store') === $st->slug ? 'selected' : '' }}>
                                {{ $st->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Category filter --}}
                    <div>
                        <label class="block text-xs font-semibold text-[var(--color-ink-3)] mb-1">Kategori</label>
                        <select name="category" class="w-full rounded-xl border border-[var(--color-paper-3)] bg-[var(--color-paper)] px-3 py-2 text-xs focus:border-[var(--color-accent)] focus:outline-none">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->slug }}" {{ request('category') === $cat->slug ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Price range --}}
                    <div class="flex gap-2 items-center">
                        <div class="flex-1">
                            <label class="block text-xs font-semibold text-[var(--color-ink-3)] mb-1">Harga Min</label>
                            <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="0" class="w-full rounded-xl border border-[var(--color-paper-3)] bg-[var(--color-paper)] px-3 py-2 text-xs focus:border-[var(--color-accent)] focus:outline-none">
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs font-semibold text-[var(--color-ink-3)] mb-1">Harga Max</label>
                            <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max" class="w-full rounded-xl border border-[var(--color-paper-3)] bg-[var(--color-paper)] px-3 py-2 text-xs focus:border-[var(--color-accent)] focus:outline-none">
                        </div>
                    </div>

                    {{-- Sort --}}
                    <div>
                        <label class="block text-xs font-semibold text-[var(--color-ink-3)] mb-1">Urutkan</label>
                        <select name="sort" class="w-full rounded-xl border border-[var(--color-paper-3)] bg-[var(--color-paper)] px-3 py-2 text-xs focus:border-[var(--color-accent)] focus:outline-none">
                            <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Terbaru</option>
                            <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Harga: Rendah ke Tinggi</option>
                            <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Harga: Tinggi ke Rendah</option>
                            <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Nama: A-Z</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    @if(request()->hasAny(['q', 'store', 'category', 'min_price', 'max_price', 'sort']))
                    <a href="{{ route('marketplace.search') }}" class="rounded-xl px-4 py-2 text-xs font-medium text-[var(--color-ink-2)] hover:bg-[var(--color-paper-2)]">
                        Reset Filter
                    </a>
                    @endif
                    <button type="submit" class="rounded-xl bg-[var(--color-accent)] px-6 py-2 text-xs font-bold text-white hover:bg-[var(--color-accent-hover)] transition-colors">
                        Terapkan Filter
                    </button>
                </div>
            </form>
        </div>

        {{-- Results Info --}}
        <div class="mb-4 flex items-center justify-between text-xs text-[var(--color-ink-3)]">
            <span>Ditemukan <strong>{{ $products->total() }}</strong> produk</span>
            @if(request('q'))
            <span>Kata kunci: <em>"{{ request('q') }}"</em></span>
            @endif
        </div>

        {{-- Products Grid --}}
        @if($products->isEmpty())
        <div class="text-center py-20 bg-white rounded-3xl border border-[var(--color-paper-3)]">
            <svg class="mx-auto text-[var(--color-ink-3)] mb-4" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <h3 class="text-base font-bold text-[var(--color-ink)]">Tidak ada produk ditemukan</h3>
            <p class="text-xs text-[var(--color-ink-2)] mt-1">Coba gunakan kata kunci yang lebih umum atau kurangi filter.</p>
        </div>
        @else
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 sm:gap-6">
            @foreach($products as $prod)
            <div class="group flex flex-col justify-between overflow-hidden rounded-2xl bg-white border border-[var(--color-paper-3)] shadow-sm transition-all hover:shadow-md">
                <div>
                    {{-- Product Image --}}
                    <div class="relative aspect-square w-full overflow-hidden bg-[var(--color-paper-2)]">
                        @if($prod->image)
                        <img src="{{ asset($prod->image) }}" alt="{{ $prod->name }}" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy">
                        @else
                        <div class="flex h-full w-full items-center justify-center text-[var(--color-ink-3)] text-xs">
                            Tidak Ada Gambar
                        </div>
                        @endif

                        @if($prod->store)
                        <span class="absolute bottom-2 left-2 rounded-lg bg-black/60 backdrop-blur-xs px-2 py-0.5 text-[10px] font-medium text-white truncate max-w-[85%]">
                            🏪 {{ $prod->store->name }}
                        </span>
                        @endif
                    </div>

                    {{-- Product Info --}}
                    <div class="p-4">
                        @if($prod->category)
                        <span class="text-[10px] font-semibold text-[var(--color-accent)] uppercase">
                            {{ $prod->category->name }}
                        </span>
                        @endif
                        <h3 class="font-bold text-xs sm:text-sm text-[var(--color-ink)] line-clamp-1 mt-0.5">
                            @if($prod->store)
                            <a href="{{ route('marketplace.product', ['storeSlug' => $prod->store->slug, 'productSlug' => $prod->slug]) }}" class="hover:underline">
                                {{ $prod->name }}
                            </a>
                            @else
                            {{ $prod->name }}
                            @endif
                        </h3>
                        <p class="text-xs text-[var(--color-ink-2)] line-clamp-2 mt-1">
                            {{ $prod->description ?? 'Produk kuliner higienis dan fresh.' }}
                        </p>
                    </div>
                </div>

                {{-- Price & Cart --}}
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
                            store_id: {{ $prod->store_id ?? ($prod->store ? $prod->store->id : 1) }},
                            store_name: '{{ addslashes($prod->store ? $prod->store->name : 'Pempek UMKM') }}'
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
