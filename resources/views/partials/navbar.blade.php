<nav
    role="navigation"
    aria-label="Navigasi utama"
    x-data="{ scrolled: false, mobileOpen: false }"
    x-init="
        window.addEventListener('scroll', () => scrolled = window.scrollY > 40, { passive: true });
        $watch('mobileOpen', (v) => { document.body.style.overflow = v ? 'hidden' : ''; });
    "
    @keydown.escape.window="mobileOpen = false"
    class="fixed top-0 left-0 right-0 z-50 transition-all duration-[var(--dur-slow)]"
    :class="scrolled ? 'bg-white/90 backdrop-blur-md shadow-[var(--shadow-sm)]' : 'bg-transparent'"
>
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-5 sm:h-20 sm:px-8">
        {{-- Wordmark — hard left --}}
        <a href="/" class="text-xl font-bold tracking-tight text-[var(--color-ink)] sm:text-2xl" aria-label="Pempek Palembang">
            Pempek Depok<span class="text-[var(--color-accent)]">.</span>
        </a>

        {{-- Desktop links --}}
        <ul class="hidden items-center gap-8 lg:flex">
            <li><a href="#beranda" class="text-sm font-medium text-[var(--color-ink-2)] transition-colors duration-[var(--dur-normal)] hover:text-[var(--color-ink)]">Beranda</a></li>
            <li><a href="#produk" class="text-sm font-medium text-[var(--color-ink-2)] transition-colors duration-[var(--dur-normal)] hover:text-[var(--color-ink)]">Menu</a></li>
            <li><a href="#tentang" class="text-sm font-medium text-[var(--color-ink-2)] transition-colors duration-[var(--dur-normal)] hover:text-[var(--color-ink)]">Tentang</a></li>
            <li><a href="#testimoni" class="text-sm font-medium text-[var(--color-ink-2)] transition-colors duration-[var(--dur-normal)] hover:text-[var(--color-ink)]">Testimoni</a></li>
            <li><a href="#faq" class="text-sm font-medium text-[var(--color-ink-2)] transition-colors duration-[var(--dur-normal)] hover:text-[var(--color-ink)]">FAQ</a></li>
            <li><a href="#artikel" class="text-sm font-medium text-[var(--color-ink-2)] transition-colors duration-[var(--dur-normal)] hover:text-[var(--color-ink)]">Artikel</a></li>
        </ul>

        {{-- CTA — hard right --}}
        <div class="flex items-center gap-3">
            {{-- Cart button --}}
            <button
                @click="$store.cart.openCart()"
                class="relative flex h-10 w-10 items-center justify-center rounded-[var(--radius-lg)] text-[var(--color-ink)] transition-colors hover:bg-[var(--color-paper-2)]"
                :aria-label="'Buka keranjang (' + $store.cart.count + ' item)'"
            >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                    <circle cx="8" cy="21" r="1" />
                    <circle cx="19" cy="21" r="1" />
                    <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12" />
                </svg>
                <template x-if="$store.cart.count > 0">
                    <span class="absolute -top-1 -right-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-[var(--color-accent)] px-1 text-[10px] font-bold text-white" x-text="$store.cart.count"></span>
                </template>
            </button>

            {{-- WA CTA — cart-aware: berisi pesanan di keranjang jika ada --}}
            <a
                :href="$store.cart.items.length > 0 ? $store.cart.waLink : '{{ $waLink }}'"
                target="_blank"
                rel="noopener noreferrer"
                class="hidden rounded-[var(--radius-xl)] bg-[var(--color-accent)] px-5 py-2.5 text-sm font-semibold text-white transition-all duration-[var(--dur-normal)] hover:bg-[var(--color-accent-hover)] active:scale-[0.98] sm:inline-flex sm:items-center sm:gap-2"
                x-text="$store.cart.items.length > 0 ? 'Pesan (' + $store.cart.count + ')' : 'Pesan Sekarang'"
            >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/>
                </svg>
                Pesan Sekarang
            </a>

            {{-- Mobile hamburger --}}
            <button
                class="flex h-10 w-10 items-center justify-center rounded-[var(--radius-lg)] text-[var(--color-ink)] transition-colors hover:bg-[var(--color-paper-2)] lg:hidden"
                @click="mobileOpen = !mobileOpen"
                :aria-label="mobileOpen ? 'Tutup menu' : 'Buka menu'"
                :aria-expanded="mobileOpen"
            >
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" aria-hidden="true">
                    <path x-show="mobileOpen" d="M18 6 6 18M6 6l12 12"/>
                    <path x-show="!mobileOpen" d="M4 7h16M4 12h16M4 17h16"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Mobile overlay --}}
    <div x-show="mobileOpen" x-cloak class="fixed inset-0 z-40 bg-black/20 backdrop-blur-sm lg:hidden" @click="mobileOpen = false" aria-hidden="true"></div>

    {{-- Mobile drawer --}}
    <div
        x-show="mobileOpen"
        x-cloak
        x-transition:enter="transition-transform duration-[var(--dur-slow)]"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition-transform duration-[var(--dur-slow)]"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed top-0 right-0 z-50 h-full w-72 bg-white shadow-[var(--shadow-xl)] lg:hidden"
        role="dialog"
        aria-label="Menu navigasi"
    >
        <div class="flex h-16 items-center justify-between border-b border-[var(--color-paper-3)] px-5">
            <span class="text-lg font-bold text-[var(--color-ink)]">Menu</span>
            <button @click="mobileOpen = false" class="flex h-9 w-9 items-center justify-center rounded-[var(--radius-md)] hover:bg-[var(--color-paper-2)]" aria-label="Tutup menu">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>
        </div>
        <nav class="px-5 py-6">
            <ul class="space-y-1">
                <li><a href="#beranda" @click="mobileOpen = false" class="block rounded-[var(--radius-lg)] px-4 py-3 text-base font-medium text-[var(--color-ink-2)] transition-colors hover:bg-[var(--color-paper-2)] hover:text-[var(--color-ink)]">Beranda</a></li>
                <li><a href="#produk" @click="mobileOpen = false" class="block rounded-[var(--radius-lg)] px-4 py-3 text-base font-medium text-[var(--color-ink-2)] transition-colors hover:bg-[var(--color-paper-2)] hover:text-[var(--color-ink)]">Menu</a></li>
                <li><a href="#tentang" @click="mobileOpen = false" class="block rounded-[var(--radius-lg)] px-4 py-3 text-base font-medium text-[var(--color-ink-2)] transition-colors hover:bg-[var(--color-paper-2)] hover:text-[var(--color-ink)]">Tentang</a></li>
                <li><a href="#testimoni" @click="mobileOpen = false" class="block rounded-[var(--radius-lg)] px-4 py-3 text-base font-medium text-[var(--color-ink-2)] transition-colors hover:bg-[var(--color-paper-2)] hover:text-[var(--color-ink)]">Testimoni</a></li>
                <li><a href="#faq" @click="mobileOpen = false" class="block rounded-[var(--radius-lg)] px-4 py-3 text-base font-medium text-[var(--color-ink-2)] transition-colors hover:bg-[var(--color-paper-2)] hover:text-[var(--color-ink)]">FAQ</a></li>
                <li><a href="#artikel" @click="mobileOpen = false" class="block rounded-[var(--radius-lg)] px-4 py-3 text-base font-medium text-[var(--color-ink-2)] transition-colors hover:bg-[var(--color-paper-2)] hover:text-[var(--color-ink)]">Artikel</a></li>
            </ul>
            <div class="mt-6 border-t border-[var(--color-paper-3)] pt-6">
                <a
                    href="{{ $waLink }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex w-full items-center justify-center gap-2 rounded-[var(--radius-xl)] bg-[var(--color-accent)] px-5 py-3 text-sm font-semibold text-white transition-all hover:bg-[var(--color-accent-hover)]"
                >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/>
                    </svg>
                    Pesan Sekarang
                </a>
            </div>
        </nav>
    </div>
</nav>
