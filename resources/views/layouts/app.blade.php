<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $seoTitle ?? 'Pempek Palembang — Pempek Asli Palembang, Lezat & Fresh Setiap Hari')</title>
    <meta name="description" content="{{ $seoDescription ?? 'Pempek asli Palembang dibuat fresh setiap hari dari ikan tenggiri pilihan dengan resep turun-temurun 3 generasi. Tanpa pengawet, pengiriman cepat ke seluruh Indonesia. Pesan via WhatsApp!' }}">
    @if(!empty($metaKeywords))
    <meta name="keywords" content="{{ $metaKeywords }}">
    @endif
    <meta name="robots" content="{{ $robots ?? 'index, follow' }}">
    <meta name="geo.region" content="ID-SS">
    <meta name="geo.placename" content="Palembang">
    <meta name="theme-color" content="#0f766e">
    <link rel="canonical" href="{{ $canonicalUrl ?? url()->current() }}">
    <link rel="alternate" hreflang="id-ID" href="{{ $canonicalUrl ?? url()->current() }}">
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:locale" content="id_ID">
    <meta property="og:site_name" content="{{ $ogSiteName ?? 'Pempek Palembang' }}">
    <meta property="og:title" content="{{ $ogTitle ?? ($seoTitle ?? 'Pempek Palembang — Pempek Asli Palembang, Lezat & Fresh Setiap Hari') }}">
    <meta property="og:description" content="{{ $ogDescription ?? $seoDescription ?? 'Pempek asli Palembang dibuat fresh setiap hari dari ikan tenggiri pilihan dengan resep turun-temurun.' }}">
    <meta property="og:url" content="{{ $canonicalUrl ?? url()->current() }}">
    <meta property="og:image" content="{{ $ogImage ?? asset('images/hero-pempek.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $ogTitle ?? ($seoTitle ?? 'Pempek Palembang — Pempek Asli Palembang, Lezat & Fresh Setiap Hari') }}">
    <meta name="twitter:description" content="{{ $ogDescription ?? $seoDescription ?? 'Pempek asli Palembang dibuat fresh setiap hari dari ikan tenggiri pilihan dengan resep turun-temurun.' }}">
    <meta name="twitter:image" content="{{ $ogImage ?? asset('images/hero-pempek.png') }}">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preload" as="image" href="{{ $ogImage ?? asset('images/hero-pempek.png') }}" fetchpriority="high">
    @if(!empty($googleVerification))
    <meta name="google-site-verification" content="{{ $googleVerification }}">
    @endif
    @if(!empty($schemaJsonLd))
    <script type="application/ld+json">{!! $schemaJsonLd !!}</script>
    @endif
    @stack('head')

    {{-- Fonts: Plus Jakarta Sans — CDN, tanpa build step --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600&display=optional" rel="stylesheet">

    {{-- Vite CSS (Tailwind 4) --}}
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-full flex flex-col antialiased" x-data>
    @include('partials.navbar')

    <main class="flex-1">
        @yield('content')
    </main>

    @include('partials.footer')
    @include('partials.cart-drawer')

    {{-- Alpine.js — CDN, tanpa build step --}}
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        // Cart store global — event bus sama persis dengan versi Next.js
        document.addEventListener('alpine:init', () => {
            Alpine.store('cart', {
                items: [],
                isOpen: false,
                toast: null,
                _timer: null,
                init() {
                    try {
                        const raw = localStorage.getItem('pempek_cart_v2');
                        if (raw) this.items = JSON.parse(raw);
                    } catch (e) {}
                    window.addEventListener('pempek:add-to-cart', (e) => {
                        this.addItem(e.detail);
                    });
                },
                get count() {
                    return this.items.reduce((s, i) => s + i.quantity, 0);
                },
                get total() {
                    return this.items.reduce((s, i) => s + i.price * i.quantity, 0);
                },
                addItem(item, qty = 1) {
                    const currentStoreId = item.store_id || item.storeId;
                    if (this.items.length > 0 && currentStoreId) {
                        const existingStoreId = this.items[0].store_id || this.items[0].storeId;
                        if (existingStoreId && existingStoreId !== currentStoreId) {
                            const storeName = item.store_name || item.storeName || 'toko lain';
                            const confirmed = confirm(
                                'Keranjang Anda saat ini berisi produk dari toko yang berbeda.\n\nPesanan UMKM diproses per masing-masing toko.\n\nKosongkan keranjang dan ganti dengan produk dari "' + storeName + '"?'
                            );
                            if (!confirmed) {
                                return;
                            }
                            this.items = [];
                        }
                    }

                    const existing = this.items.find(i => i.id === item.id);
                    if (existing) {
                        existing.quantity = Math.min(existing.quantity + qty, item.stock || 99);
                    } else {
                        this.items.push({ ...item, quantity: Math.min(qty, item.stock || 99) });
                    }
                    this.persist();
                    this.showToast('✓ ' + item.name + ' masuk keranjang');
                },
                removeItem(id) {
                    this.items = this.items.filter(i => i.id !== id);
                    this.persist();
                },
                setQuantity(id, qty) {
                    this.items = this.items
                        .map(i => i.id === id ? { ...i, quantity: Math.max(1, Math.min(qty, i.stock || 99)) } : i)
                        .filter(i => i.quantity > 0);
                    this.persist();
                },
                persist() {
                    try { localStorage.setItem('pempek_cart_v2', JSON.stringify(this.items)); } catch (e) {}
                },
                showToast(msg) {
                    this.toast = msg;
                    if (this._timer) clearTimeout(this._timer);
                    this._timer = setTimeout(() => this.toast = null, 2500);
                },
                openCart() { this.isOpen = true; },
                closeCart() { this.isOpen = false; },
                checkoutQris() {
                    if (!this.items.length) { alert('Keranjang masih kosong.'); return; }
                    this.closeCart();
                    window.location.href = '/checkout';
                },
                get waMessage() {
                    return this.items.map(i => `• ${i.name} x${i.quantity} = ${this.formatIDR(i.price * i.quantity)}`).join('\n');
                },
                get waLink() {
                    const num = '{{ $waNumber }}';
                    const text = `Halo, saya ingin memesan:\n${this.waMessage}\n\nTotal: ${this.formatIDR(this.total)}`;
                    return `https://wa.me/${num}?text=${encodeURIComponent(text)}`;
                },
                formatIDR(n) {
                    return 'Rp' + n.toLocaleString('id-ID');
                }
            });
        });
    </script>
    {{-- Visitor tracker — kirim page view ke /api/track (idempoten per hari) --}}
    <script>
        (function () {
            var sent = false;
            function track() {
                if (sent) return;
                sent = true;
                try {
                    fetch('/api/track', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({ path: window.location.pathname + window.location.search }),
                    });
                } catch (e) {}
            }
            if (document.readyState === 'complete') track();
            else window.addEventListener('load', track);
        })();
    </script>
    @stack('scripts')
</body>
</html>
