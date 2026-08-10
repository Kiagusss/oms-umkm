@extends('admin.layouts.app')

@section('title', 'Kasir — Admin Pempek')

@section('content')
<div x-data="posApp()" x-init="init()" class="flex flex-col gap-5 lg:flex-row">
    {{-- KOLOM KIRI: Produk --}}
    <div class="flex-1">
        <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-extrabold tracking-tight text-slate-800">Kasir</h1>
                <p class="text-xs font-medium text-slate-400">{{ now()->translatedFormat('l, d F Y') }}</p>
            </div>
            <div class="relative sm:w-72">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.3-4.3"/></svg>
                </span>
                <input x-model="search" type="text" placeholder="Cari produk…"
                    class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-8 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                <button x-show="search" @click="search = ''" class="absolute inset-y-0 right-2.5 flex items-center text-slate-400 hover:text-slate-600" aria-label="Bersihkan pencarian">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Kategori --}}
        <div class="mb-5 flex gap-2 overflow-x-auto pb-1 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
            <button @click="selectedCategory = null"
                class="shrink-0 rounded-full px-4 py-2 text-sm font-semibold transition"
                :class="selectedCategory === null ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50'">
                Semua
            </button>
            @foreach($categories as $cat)
            <button @click="selectedCategory = {{ $cat->id }}"
                class="shrink-0 rounded-full px-4 py-2 text-sm font-semibold transition"
                :class="selectedCategory === {{ $cat->id }} ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50'">
                {{ $cat->name }}
            </button>
            @endforeach
        </div>

        {{-- Grid Produk --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
            @foreach($products as $product)
            <button @click="addToCart({{ $product->id }})" {{ $product->stock <= 0 ? 'disabled' : '' }}
                class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white text-left transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-md active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50">
                <div class="relative aspect-[4/3] overflow-hidden bg-slate-100">
                    <img src="{{ $product->thumbnail ?? '/images/hero-pempek.png' }}"
                         alt="{{ $product->name }}"
                         class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                    @if($product->stock <= 0)
                    <span class="absolute inset-0 flex items-center justify-center bg-white/70 text-xs font-bold text-slate-500">Stok Habis</span>
                    @elseif($product->stock <= 5)
                    <span class="absolute left-2 top-2 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700">Sisa {{ $product->stock }}</span>
                    @endif
                </div>
                <div class="flex flex-1 flex-col p-3">
                    <h3 class="truncate text-sm font-bold text-slate-800">{{ $product->name }}</h3>
                    <p class="mt-0.5 text-xs text-slate-400">Stok: {{ $product->stock }}</p>
                    <div class="mt-2 flex items-center justify-between gap-1">
                        <span class="text-sm font-extrabold text-emerald-600">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100 transition group-hover:bg-emerald-600 group-hover:text-white">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                        </span>
                    </div>
                </div>
            </button>
            @endforeach
        </div>
    </div>

    {{-- KOLOM KANAN: Pesanan --}}
    <div class="lg:w-80 lg:shrink-0">
        <div class="flex flex-col rounded-2xl border border-slate-200/80 bg-white shadow-sm lg:sticky lg:top-24">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h2 class="text-base font-extrabold text-slate-800">
                    Pesanan
                    <span x-show="cartCount > 0" class="ml-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-700" x-text="cartCount"></span>
                </h2>
                <button @click="cart = []" x-show="cart.length > 0" class="text-xs font-semibold text-slate-400 transition hover:text-rose-500">Kosongkan</button>
            </div>

            <div class="px-5 pt-4">
                <input x-model="customer" type="text" placeholder="Nama pembeli (opsional)"
                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
            </div>

            <div class="flex-1 overflow-y-auto px-5 py-4">
                <template x-for="item in cart" :key="item.productId">
                    <div class="flex items-center gap-3 py-3">
                        <img :src="item.thumbnail || '/images/hero-pempek.png'" class="h-12 w-12 shrink-0 rounded-xl object-cover ring-1 ring-slate-100">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-slate-800" x-text="item.productName"></p>
                            <p class="text-xs text-slate-400" x-text="'Rp ' + formatNumber(item.price) + ' / pcs'"></p>
                            <div class="mt-1.5 inline-flex items-center gap-1 rounded-lg bg-slate-50 ring-1 ring-slate-200">
                                <button @click.stop="updateQty(item.productId, -1)" class="flex h-7 w-7 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-200" aria-label="Kurangi">−</button>
                                <span class="w-6 text-center text-sm font-bold tabular-nums text-slate-800" x-text="item.quantity"></span>
                                <button @click.stop="updateQty(item.productId, 1)" class="flex h-7 w-7 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-200" aria-label="Tambah">+</button>
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            <span class="text-sm font-bold tabular-nums text-slate-800" x-text="'Rp ' + formatNumber(item.price * item.quantity)"></span>
                            <button @click.stop="updateQty(item.productId, -999)" class="text-[11px] font-medium text-slate-300 transition hover:text-rose-500">Hapus</button>
                        </div>
                    </div>
                </template>

                <div x-show="cart.length === 0" class="flex flex-col items-center justify-center py-14 text-center text-slate-400">
                    <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-slate-50 ring-1 ring-slate-100">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                    </div>
                    <p class="text-sm font-semibold">Keranjang kosong</p>
                    <p class="text-xs">Ketuk produk untuk menambahkan</p>
                </div>
            </div>

            <div class="border-t border-slate-100 px-5 py-4">
                <div class="space-y-1.5 text-sm">
                    <div class="flex justify-between text-slate-500">
                        <span>Subtotal</span>
                        <span class="font-semibold tabular-nums text-slate-700" x-text="'Rp ' + formatNumber(subtotal)"></span>
                    </div>
                    <div class="flex justify-between text-slate-500">
                        <span>Item</span>
                        <span class="font-semibold tabular-nums text-slate-700" x-text="cartCount + ' pcs'"></span>
                    </div>
                    <div class="flex items-baseline justify-between border-t border-slate-100 pt-2.5">
                        <span class="text-sm font-semibold text-slate-700">Total</span>
                        <span class="text-xl font-extrabold tabular-nums text-emerald-600" x-text="'Rp ' + formatNumber(total)"></span>
                    </div>
                </div>
                <button @click="processTransaction()" :disabled="cart.length === 0"
                    class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 py-3.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-500 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-40">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/></svg>
                    <span x-text="busy ? 'Memproses…' : 'Bayar Sekarang'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('posApp', () => ({
        products: @json($products->keyBy('id')),
        search: '',
        selectedCategory: null,
        customer: '',
        cart: [],
        busy: false,

        init() {
            this.$watch('search', () => this.debouncedFilter());
            this.$watch('selectedCategory', () => this.debouncedFilter());
        },

        addToCart(productId) {
            const product = this.findProduct(productId);
            if (!product || product.stock <= 0) return;
            const existing = this.cart.find(item => item.productId === productId);
            if (existing) {
                this.updateQty(productId, 1);
            } else {
                this.cart.push({
                    productId: product.id,
                    productName: product.name,
                    price: product.price,
                    quantity: 1,
                    thumbnail: product.thumbnail,
                    stock: product.stock
                });
            }
        },

        updateQty(productId, delta) {
            const idx = this.cart.findIndex(item => item.productId === productId);
            if (idx === -1) return;
            const item = this.cart[idx];
            const newQty = item.quantity + delta;
            if (newQty <= 0) {
                this.cart.splice(idx, 1);
            } else {
                this.cart[idx].quantity = newQty;
            }
        },

        async processTransaction() {
            if (this.cart.length === 0) return;
            this.busy = true;
            try {
                const payload = {
                    customer_name: this.customer,
                    items: this.cart.map(item => ({ id: item.productId, quantity: item.quantity })),
                    payment_method: 'Tunai',
                    cash_received: this.total
                };
                const response = await fetch('/api/pos-checkout', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify(payload)
                });
                const data = await response.json();
                if (response.ok) {
                    alert('Transaksi berhasil: ' + data.order_id);
                    this.cart = [];
                    this.customer = '';
                } else {
                    alert('Error: ' + (data.error || 'Transaksi gagal'));
                }
            } catch (err) {
                alert('Koneksi error: ' + err.message);
            } finally {
                this.busy = false;
            }
        },

        findProduct(id) {
            return this.products[id];
        },

        get cartCount() {
            return this.cart.reduce((sum, item) => sum + item.quantity, 0);
        },

        get subtotal() {
            return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        },

        get total() {
            return this.subtotal;
        },

        formatNumber(num) {
            return num.toLocaleString('id-ID');
        },

        debouncedFilter() {
            // dummy — category filter via backend
        }
    }));
});
</script>
@endpush
@endsection