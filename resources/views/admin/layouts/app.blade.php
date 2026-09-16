<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard — Admin Pempek')</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="preload" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600&display=optional" as="style" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600&display=optional" rel="stylesheet">

    {{-- Vite CSS (Tailwind 4 + Sequence palette) --}}
    @vite(['resources/css/app.css'])

    @stack('styles')
</head>
<body class="min-h-full bg-[var(--color-bg)] font-sans antialiased text-[var(--color-ink-2)]">
    @php
        // Fetch stats directly for sidebar badges to ensure they work on all admin pages
        $productCount = \App\Models\Product::count();
        $pendingOrderCount = \App\Models\Order::where('status', 'pending')->count();

        $menuMain = [
            ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'M3 12l9-9 9 9M5 10v10h14V10', 'badge' => null],
            ['label' => 'POS', 'route' => 'admin.pos', 'icon' => 'M5 4h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5a1 1 0 011-1zm3 4h8M8 12h8M8 16h5', 'badge' => null],
            ['label' => 'Produk', 'route' => 'admin.produk.index', 'icon' => 'M21 8l-9-5-9 5 9 5 9-5zm-9 5v9m9-14v9M3 5v9', 'badge' => $productCount > 0 ? $productCount . '+' : null],
            ['label' => 'Paket', 'route' => 'admin.paket.index', 'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z', 'badge' => null],
            ['label' => 'Kategori', 'route' => 'admin.kategori.index', 'icon' => 'M3 7h18M3 12h18M3 17h18', 'badge' => null],
            ['label' => 'Artikel', 'route' => 'admin.artikel.index', 'icon' => 'M4 5h16v14H4zM8 9h8M8 13h8', 'badge' => null],
            ['label' => 'Pesanan', 'route' => 'admin.pesanan.index', 'icon' => 'M6 3h12l2 18H4L6 3zm4 8h4', 'badge' => $pendingOrderCount > 0 ? $pendingOrderCount . '+' : null],
            ['label' => 'Voucher', 'route' => 'admin.voucher.index', 'icon' => 'M20 12a8 8 0 11-16 0 8 8 0 0116 0zm-8-6v12m4-8H8', 'badge' => null],
            ['label' => 'Ulasan', 'route' => 'admin.review.index', 'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z', 'badge' => null],
        ];

        $menuOps = [
            ['label' => 'Cabang', 'route' => 'admin.cabang.index', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
            ['label' => 'Bahan & Stok', 'route' => 'admin.inventori.index', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
            ['label' => 'Resep & HPP', 'route' => 'admin.resep.index', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
            ['label' => 'Supplier', 'route' => 'admin.supplier.index', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
            ['label' => 'Pembelian PO', 'route' => 'admin.pembelian.index', 'icon' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'],
            ['label' => 'Biaya Ops', 'route' => 'admin.biaya.index', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['label' => 'Transfer Stok', 'route' => 'admin.transfer.index', 'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4'],
            ['label' => 'Laba Rugi', 'route' => 'admin.laporan.keuangan', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
            ['label' => 'Pengguna & Hak', 'route' => 'admin.pengguna.index', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
        ];

        $menuGeneral = [
            ['label' => 'Testimoni', 'route' => 'admin.testimoni.index', 'icon' => 'M12 2a5 5 0 015 5c0 2-1.5 3.5-3 4.5V14h-4v-2.5C8.5 10.5 7 9 7 7a5 5 0 015-5zm-4 14h8v2H8z'],
            ['label' => 'FAQ', 'route' => 'admin.faq.index', 'icon' => 'M12 8a3 3 0 10-3 3c.7 0 1.3.3 1.7.8L12 14m0-6l4-4'],
            ['label' => 'Banner', 'route' => 'admin.banner.index', 'icon' => 'M4 5h16v14H4zM8 9h8M8 13h8'],
            ['label' => 'Galeri', 'route' => 'admin.galeri.index', 'icon' => 'M4 5h16v14H4zM8 9h1m7 8l-4-4-3 3-2-2'],
            ['label' => 'Pengaturan', 'route' => 'admin.pengaturan', 'icon' => 'M12 15a3 3 0 100-6 3 3 0 000 6zm7.4-3a7.4 7.4 0 00-.1-1.2l2-1.5-2-3.4-2.3 1a7.6 7.6 0 00-2-1.2L14.5 3h-5l-.5 2.4a7.6 7.6 0 00-2 1.2l-2.3-1-2 3.4 2 1.5a7.4 7.4 0 000 2.4l-2 1.5 2 3.4 2.3-1a7.6 7.6 0 002 1.2l.5 2.4h5l.5-2.4a7.6 7.6 0 002-1.2l2.3 1 2-3.4-2-1.5c.1-.4.1-.8.1-1.2z'],
            ['label' => 'SEO', 'route' => 'admin.seo', 'icon' => 'M11 4a7 7 0 100 14 7 7 0 000-14zm8 18l-4.5-4.5'],
            ['label' => 'AI Assistant', 'route' => 'admin.ai', 'icon' => 'M12 2a10 10 0 100 20 10 10 0 000-20zm-1 15h2v2h-2zm1.6-9.5c1 .3 1.7 1.1 1.7 2.2 0 1.2-.7 1.8-1.7 2.3-.7.4-.9.7-.9 1.4V14h-1.4v-.7c0-1.1.5-1.7 1.5-2.2.8-.4 1.1-.7 1.1-1.4 0-.6-.4-1-1.2-1-.7 0-1.2.3-1.4 1L9.7 9.4C10.1 8.3 11.2 7.5 12.6 7.5z'],
        ];

        $menuPlatform = [
            ['label' => 'Import Katalog', 'route' => 'admin.import.index', 'icon' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12'],
            ['label' => 'Riwayat Import', 'route' => 'admin.import.history', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['label' => 'Platform UMKM', 'route' => 'admin.platform.index', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
        ];

        $current = request()->route()?->getName() ?? '';

        $isActiveFn = function($itemRoute) use ($current) {
            if ($current === $itemRoute) return true;
            $prefix = match($itemRoute) {
                'admin.produk.index' => 'admin.produk',
                'admin.paket.index' => 'admin.paket',
                'admin.kategori.index' => 'admin.kategori',
                'admin.artikel.index' => 'admin.artikel',
                'admin.testimoni.index' => 'admin.testimoni',
                'admin.faq.index' => 'admin.faq',
                'admin.banner.index' => 'admin.banner',
                'admin.galeri.index' => 'admin.galeri',
                'admin.pesanan.index' => 'admin.pesanan',
                'admin.voucher.index' => 'admin.voucher',
                'admin.review.index' => 'admin.review',
                'admin.cabang.index' => 'admin.cabang',
                'admin.inventori.index' => 'admin.inventori',
                'admin.resep.index' => 'admin.resep',
                'admin.supplier.index' => 'admin.supplier',
                'admin.pembelian.index' => 'admin.pembelian',
                'admin.biaya.index' => 'admin.biaya',
                'admin.transfer.index' => 'admin.transfer',
                'admin.pengguna.index' => 'admin.pengguna',
                'admin.laporan.keuangan' => 'admin.laporan',
                'admin.import.index' => 'admin.import',
                'admin.import.history' => 'admin.import.history',
                'admin.platform.stores' => 'admin.platform',
                default => null,
            };
            return $prefix ? str_starts_with($current, $prefix) : false;
        };
    @endphp

    <div class="flex min-h-screen">
        {{-- Sidebar Desktop --}}
        <aside class="fixed inset-y-0 left-0 z-40 hidden w-72 flex-col border-r border-slate-100 bg-white lg:flex">
            {{-- Logo branding --}}
            <div class="flex h-20 items-center gap-3 px-8">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 21a9 9 0 100-18 9 9 0 000 18z"/>
                        <path d="M12 7c-2 0-3 1.5-3 3.5 0 2.5 3 4.5 3 4.5s3-2 3-4.5C15 8.5 14 7 12 7z" stroke="#22c55e" stroke-width="2"/>
                    </svg>
                </div>
                <span class="text-xl font-extrabold tracking-tight text-slate-800">Donezo</span>
            </div>

            {{-- Nav menu --}}
            <nav class="flex-1 overflow-y-auto px-6 py-4">
                {{-- MENU Section --}}
                <div class="mb-6">
                    <p class="px-4 text-[11px] font-bold tracking-wider text-slate-400 uppercase">Menu</p>
                    <ul class="mt-2 space-y-1">
                        @foreach($menuMain as $item)
                            @php $active = $isActiveFn($item['route']); @endphp
                            <li>
                                <a href="{{ route($item['route']) }}"
                                   class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition-all {{ $active ? 'bg-emerald-50/70 text-emerald-800 border-l-4 border-emerald-700 pl-3' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 {{ $active ? 'text-emerald-700' : 'text-slate-400 group-hover:text-slate-500' }}">
                                        <path d="{{ $item['icon'] }}" />
                                    </svg>
                                    <span>{{ $item['label'] }}</span>
                                    @if($item['badge'])
                                        <span class="ml-auto rounded-lg bg-emerald-950 px-2 py-0.5 text-xs font-bold text-emerald-400">
                                            {{ $item['badge'] }}
                                        </span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- OPERASIONAL Section --}}
                <div class="mb-6">
                    <p class="px-4 text-[11px] font-bold tracking-wider text-slate-400 uppercase">Operasional & Bisnis</p>
                    <ul class="mt-2 space-y-1">
                        @foreach($menuOps as $item)
                            @php $active = $isActiveFn($item['route']); @endphp
                            <li>
                                <a href="{{ route($item['route']) }}"
                                   class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-semibold transition-all {{ $active ? 'bg-emerald-50/70 text-emerald-800 border-l-4 border-emerald-700 pl-3' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 {{ $active ? 'text-emerald-700' : 'text-slate-400 group-hover:text-slate-500' }}">
                                        <path d="{{ $item['icon'] }}" />
                                    </svg>
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- GENERAL Section --}}
                <div class="mb-6">
                    <p class="px-4 text-[11px] font-bold tracking-wider text-slate-400 uppercase">General</p>
                    <ul class="mt-2 space-y-1">
                        @foreach($menuGeneral as $item)
                            @php $active = $isActiveFn($item['route']); @endphp
                            <li>
                                <a href="{{ route($item['route']) }}"
                                   class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition-all {{ $active ? 'bg-emerald-50/70 text-emerald-800 border-l-4 border-emerald-700 pl-3' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 {{ $active ? 'text-emerald-700' : 'text-slate-400 group-hover:text-slate-500' }}">
                                        <path d="{{ $item['icon'] }}" />
                                    </svg>
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- PLATFORM & MULTI-TENANT Section --}}
                <div class="mb-6">
                    <p class="px-4 text-[11px] font-bold tracking-wider text-emerald-600 uppercase">Multi-Tenant & Import</p>
                    <ul class="mt-2 space-y-1">
                        @foreach($menuPlatform as $item)
                            @php $active = $isActiveFn($item['route']); @endphp
                            <li>
                                <a href="{{ route($item['route']) }}"
                                   class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-semibold transition-all {{ $active ? 'bg-emerald-50/70 text-emerald-800 border-l-4 border-emerald-700 pl-3' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 {{ $active ? 'text-emerald-700' : 'text-slate-400 group-hover:text-slate-500' }}">
                                        <path d="{{ $item['icon'] }}" />
                                    </svg>
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </nav>

            {{-- Download App Card & Logout --}}
            <div class="p-4">
                {{-- Download our Mobile App Card --}}
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-b from-[#06180e] to-[#0c2f1b] p-5 text-white shadow-md mb-4 wave-bg">
                    <div class="relative z-10 flex flex-col gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-900/60 text-emerald-400">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2" /><path d="M12 18h.01" /></svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold tracking-tight">Download our Mobile App</h4>
                            <p class="mt-1 text-[11px] text-emerald-400/80">Get easy in another way</p>
                        </div>
                        <a href="#" class="mt-1 block w-full rounded-xl bg-emerald-800 py-2.5 text-center text-xs font-bold text-white transition hover:bg-emerald-700">Download</a>
                    </div>
                </div>

                {{-- Logout Button --}}
                <form method="POST" action="{{ route('admin.logout') }}" class="border-t border-slate-100 pt-3">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold text-rose-600 transition hover:bg-rose-50/50">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9" />
                        </svg>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- Mobile Wrapper --}}
        <div x-data="{ sidebarOpen: false }" class="min-h-screen flex-1 lg:pl-72">
            
            {{-- Header (Topbar) — fixed full-width di atas semua --}}
            <header class="fixed top-0 left-0 right-0 z-50 flex h-20 items-center justify-between border-b border-slate-100 bg-white px-6 md:px-8 lg:pl-80">
                
                {{-- Logo for mobile & Burger --}}
                <div class="flex items-center gap-3 lg:hidden">
                    <button @click="sidebarOpen = !sidebarOpen" class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50" aria-label="Open menu">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 21a9 9 0 100-18 9 9 0 000 18z"/>
                                <path d="M12 7c-2 0-3 1.5-3 3.5 0 2.5 3 4.5 3 4.5s3-2 3-4.5C15 8.5 14 7 12 7z" stroke="#22c55e" stroke-width="2"/>
                            </svg>
                        </div>
                        <span class="text-lg font-bold tracking-tight text-slate-800">Donezo</span>
                    </div>
                </div>

                {{-- Search Bar (Desktop) --}}
                <div class="relative hidden w-80 md:block lg:w-96">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8" /><path d="M21 21l-4.3-4.3" /></svg>
                    </span>
                    <input type="text" placeholder="Search task" class="w-full rounded-full border border-slate-100 bg-slate-50/80 py-2.5 pl-11 pr-14 text-sm outline-none transition focus:border-emerald-600 focus:bg-white" />
                    <span class="absolute inset-y-0 right-2 flex items-center">
                        <kbd class="rounded-md border border-slate-200 bg-white px-2 py-0.5 text-[10px] text-slate-400 font-mono shadow-sm">⌘F</kbd>
                    </span>
                </div>

                {{-- Right utility bar --}}
                <div class="flex items-center gap-3">
                    {{-- Store Switcher (Multi-Tenant) --}}
                    @php 
                        $platformStores = \App\Models\Store::where('is_active', true)->orderBy('name')->get(); 
                        $activeStoreId = session('selected_store_id') ?? ($currentStore->id ?? ($platformStores->first()?->id ?? 1));
                    @endphp
                    @if($platformStores->isNotEmpty())
                        <form method="POST" action="{{ route('admin.platform.switch') }}" class="flex items-center">
                            @csrf
                            <div class="flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50/90 px-3 py-1.5 text-xs text-emerald-800 shadow-xs">
                                <span class="text-xs">🏪</span>
                                <select name="store_id" onchange="this.form.submit()" class="bg-transparent border-0 text-xs font-bold text-emerald-950 cursor-pointer outline-none pr-1">
                                    @foreach($platformStores as $st)
                                        <option value="{{ $st->id }}" {{ $activeStoreId == $st->id ? 'selected' : '' }}>
                                            {{ $st->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    @endif

                    {{-- Branch Switcher --}}
                    @php $allBranches = \App\Models\Branch::where('is_active', true)->get(); @endphp
                    @if($allBranches->isNotEmpty())
                        <form method="POST" action="{{ route('admin.switch-branch') }}" class="flex items-center">
                            @csrf
                            <div class="flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50/80 px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-100 transition shadow-xs">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-emerald-600 shrink-0"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                <select name="branch_id" onchange="this.form.submit()" class="bg-transparent border-0 text-xs font-bold text-slate-800 cursor-pointer outline-none pr-1">
                                    <option value="">Semua Cabang</option>
                                    @foreach($allBranches as $b)
                                        <option value="{{ $b->id }}" {{ session('selected_branch_id') == $b->id ? 'selected' : '' }}>
                                            {{ $b->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    @endif

                    {{-- Mail Icon --}}
                    <button class="hidden h-10 w-10 items-center justify-center rounded-full border border-slate-100 text-slate-500 hover:bg-slate-50 sm:flex transition shadow-sm">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" /><path d="M22 6l-10 7L2 6" /></svg>
                    </button>
                    {{-- Notification Bell --}}
                    <button class="relative flex h-10 w-10 items-center justify-center rounded-full border border-slate-100 text-slate-500 hover:bg-slate-50 transition shadow-sm">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0" /></svg>
                        <span class="absolute top-2 right-2.5 h-2 w-2 rounded-full bg-emerald-600"></span>
                    </button>

                    {{-- Profile Info --}}
                    <div class="flex items-center gap-3 border-l border-slate-100 pl-4">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()?->name ?? 'Totok Michael') }}&background=0f4a2d&color=fff&bold=true" class="h-10 w-10 rounded-full border border-slate-200 object-cover" alt="User Avatar" />
                        <div class="hidden text-left xl:block">
                            <p class="text-xs font-bold text-slate-800 leading-none">{{ Auth::user()?->name ?? 'Totok Michael' }}</p>
                            <p class="mt-1 text-[10px] text-slate-400 font-semibold leading-none">{{ Auth::user()?->email ?? 'tmichael20@mail.com' }}</p>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Mobile Drawer (Sidebar on mobile) --}}
            <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-50 lg:hidden">
                <div class="absolute inset-0 bg-black/40 backdrop-blur-xs transition-opacity duration-300" @click="sidebarOpen = false"></div>
                <aside class="absolute inset-y-0 left-0 flex w-80 flex-col bg-white shadow-2xl transition-transform duration-300">
                    <div class="flex h-20 items-center justify-between border-b border-slate-100 px-6">
                        <div class="flex items-center gap-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 21a9 9 0 100-18 9 9 0 000 18z"/>
                                    <path d="M12 7c-2 0-3 1.5-3 3.5 0 2.5 3 4.5 3 4.5s3-2 3-4.5C15 8.5 14 7 12 7z" stroke="#22c55e" stroke-width="2"/>
                                </svg>
                            </div>
                            <span class="text-lg font-bold tracking-tight text-slate-800">Donezo</span>
                        </div>
                        <button @click="sidebarOpen = false" class="flex h-8 w-8 items-center justify-center rounded-lg hover:bg-slate-100" aria-label="Close menu">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12" /></svg>
                        </button>
                    </div>
                    
                    <nav class="flex-1 overflow-y-auto px-4 py-4">
                        <div class="mb-6">
                            <p class="px-4 text-[10px] font-bold tracking-wider text-slate-400 uppercase">Menu</p>
                            <ul class="mt-2 space-y-1">
                                @foreach($menuMain as $item)
                                    @php $active = $isActiveFn($item['route']); @endphp
                                    <li>
                                        <a href="{{ route($item['route']) }}" @click="sidebarOpen = false"
                                           class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold {{ $active ? 'bg-emerald-50/70 text-emerald-800 border-l-4 border-emerald-700 pl-3' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-850' }}">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0 {{ $active ? 'text-emerald-700' : 'text-slate-400' }}">
                                                <path d="{{ $item['icon'] }}" />
                                            </svg>
                                            <span>{{ $item['label'] }}</span>
                                            @if($item['badge'])
                                                <span class="ml-auto rounded-lg bg-emerald-950 px-2 py-0.5 text-xs font-bold text-emerald-400">
                                                    {{ $item['badge'] }}
                                                </span>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="mb-6">
                            <p class="px-4 text-[10px] font-bold tracking-wider text-slate-400 uppercase">Operasional & Bisnis</p>
                            <ul class="mt-2 space-y-1">
                                @foreach($menuOps as $item)
                                    @php $active = $isActiveFn($item['route']); @endphp
                                    <li>
                                        <a href="{{ route($item['route']) }}" @click="sidebarOpen = false"
                                           class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-semibold {{ $active ? 'bg-emerald-50/70 text-emerald-800 border-l-4 border-emerald-700 pl-3' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-850' }}">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0 {{ $active ? 'text-emerald-700' : 'text-slate-400' }}">
                                                <path d="{{ $item['icon'] }}" />
                                            </svg>
                                            <span>{{ $item['label'] }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        
                        <div class="mb-6">
                            <p class="px-4 text-[10px] font-bold tracking-wider text-slate-400 uppercase">General</p>
                            <ul class="mt-2 space-y-1">
                                @foreach($menuGeneral as $item)
                                    @php $active = $isActiveFn($item['route']); @endphp
                                    <li>
                                        <a href="{{ route($item['route']) }}" @click="sidebarOpen = false"
                                           class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold {{ $active ? 'bg-emerald-50/70 text-emerald-800 border-l-4 border-emerald-700 pl-3' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-850' }}">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0 {{ $active ? 'text-emerald-700' : 'text-slate-400' }}">
                                                <path d="{{ $item['icon'] }}" />
                                            </svg>
                                            <span>{{ $item['label'] }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="mb-6">
                            <p class="px-4 text-[10px] font-bold tracking-wider text-emerald-600 uppercase">Multi-Tenant & Import</p>
                            <ul class="mt-2 space-y-1">
                                @foreach($menuPlatform as $item)
                                    @php $active = $isActiveFn($item['route']); @endphp
                                    <li>
                                        <a href="{{ route($item['route']) }}" @click="sidebarOpen = false"
                                           class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-semibold {{ $active ? 'bg-emerald-50/70 text-emerald-800 border-l-4 border-emerald-700 pl-3' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-850' }}">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0 {{ $active ? 'text-emerald-700' : 'text-slate-400' }}">
                                                <path d="{{ $item['icon'] }}" />
                                            </svg>
                                            <span>{{ $item['label'] }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </nav>
                    
                    <div class="p-4 border-t border-slate-100">
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold text-rose-600 hover:bg-rose-50/50">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9" />
                                </svg>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </aside>
            </div>

            {{-- Main Content Area --}}
            <main class="p-6 pt-24 md:p-8 md:pt-24">
                @if(session('success'))
                    <div class="mb-6 flex items-center justify-between rounded-2xl border border-emerald-200 bg-emerald-50/50 px-5 py-4 text-sm font-semibold text-emerald-800 shadow-sm animate-fade-up">
                        <div class="flex items-center gap-2">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-emerald-600"><path d="M22 11.08V12a10 10 0 11-5.93-9.14" /><path d="M22 4L12 14.01l-3-3" /></svg>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 text-lg leading-none" aria-label="Tutup">×</button>
                    </div>
                @endif
                @if($errors->any())
                    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50/50 px-5 py-4 text-sm font-semibold text-red-800 shadow-sm animate-fade-up">
                        <div class="flex items-start gap-2">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="mt-0.5 text-red-650"><circle cx="12" cy="12" r="10" /><path d="M12 8v4M12 16h.01" /></svg>
                            <ul class="list-inside list-disc space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('scripts')
</body>
</html>
