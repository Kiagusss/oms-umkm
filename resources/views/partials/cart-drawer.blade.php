{{-- Cart Drawer — Alpine store 'cart' --}}
<div
    x-data
    x-show="$store.cart.isOpen"
    x-cloak
    class="fixed inset-0 z-[100]"
    role="dialog"
    aria-modal="true"
    aria-label="Keranjang belanja"
>
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="$store.cart.closeCart()"></div>

    {{-- Panel --}}
    <div
        x-transition:enter="transition-transform duration-[var(--dur-slow)]"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition-transform duration-[var(--dur-slow)]"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="absolute right-0 top-0 flex h-full w-full max-w-md flex-col bg-white shadow-[var(--shadow-xl)]"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-[var(--color-paper-3)] px-5 py-4">
            <h2 class="text-base font-semibold text-[var(--color-ink)]">
                Keranjang <span x-show="$store.cart.count > 0" class="text-[var(--color-ink-3)]" x-text="'(' + $store.cart.count + ' item)'"></span>
            </h2>
            <button @click="$store.cart.closeCart()" aria-label="Tutup keranjang" class="flex h-8 w-8 items-center justify-center rounded-[var(--radius-lg)] text-[var(--color-ink-3)] transition-colors hover:bg-[var(--color-paper-2)] hover:text-[var(--color-ink)]">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Empty state --}}
        <div x-show="$store.cart.items.length === 0" class="flex flex-1 flex-col items-center justify-center gap-3 px-6 text-center">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-[var(--color-paper-2)] text-2xl">🛒</div>
            <p class="text-sm font-medium text-[var(--color-ink-2)]">Keranjang masih kosong</p>
            <p class="text-xs text-[var(--color-ink-3)]">Klik "Pesan" pada menu untuk menambahkan.</p>
        </div>

        {{-- Items --}}
        <div x-show="$store.cart.items.length > 0" class="flex-1 overflow-y-auto px-5 py-4">
            <ul class="space-y-4">
                <template x-for="item in $store.cart.items" :key="item.id">
                    <li class="flex gap-3">
                        <div class="relative h-16 w-16 shrink-0 overflow-hidden rounded-[var(--radius-lg)] bg-[var(--color-paper-2)]">
                            <img x-show="item.thumbnail" :src="item.thumbnail" :alt="item.name" class="h-full w-full object-cover">
                        </div>
                        <div class="flex flex-1 flex-col">
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-sm font-semibold text-[var(--color-ink)] leading-snug" x-text="item.name"></p>
                                <button @click="$store.cart.removeItem(item.id)" :aria-label="'Hapus ' + item.name" class="text-[var(--color-ink-3)] transition-colors hover:text-[var(--color-danger)]">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/></svg>
                                </button>
                            </div>
                            <p class="mt-0.5 text-sm font-semibold text-[var(--color-accent)]" x-text="$store.cart.formatIDR(item.price)"></p>
                            <div class="mt-auto flex items-center gap-2 pt-1">
                                <button @click="$store.cart.setQuantity(item.id, item.quantity - 1)" class="flex h-6 w-6 items-center justify-center rounded-[var(--radius-md)] border border-[var(--color-paper-3)] text-sm text-[var(--color-ink-2)] transition-colors hover:border-[var(--color-accent)] hover:text-[var(--color-accent)]" aria-label="Kurangi jumlah">−</button>
                                <span class="w-8 text-center text-sm font-semibold tabular-nums text-[var(--color-ink)]" x-text="item.quantity"></span>
                                <button @click="$store.cart.setQuantity(item.id, item.quantity + 1)" :disabled="item.quantity >= (item.stock || 99)" class="flex h-6 w-6 items-center justify-center rounded-[var(--radius-md)] border border-[var(--color-paper-3)] text-sm text-[var(--color-ink-2)] transition-colors hover:border-[var(--color-accent)] hover:text-[var(--color-accent)] disabled:cursor-not-allowed disabled:opacity-40" aria-label="Tambah jumlah">+</button>
                                <span x-show="item.quantity >= (item.stock || 99)" class="text-[10px] text-[var(--color-ink-3)]">stok maks</span>
                            </div>
                        </div>
                    </li>
                </template>
            </ul>
        </div>

        {{-- Footer --}}
        <div x-show="$store.cart.items.length > 0" class="border-t border-[var(--color-paper-3)] px-5 py-4">
            <div class="mb-3 flex items-center justify-between">
                <span class="text-sm text-[var(--color-ink-2)]">Total</span>
                <span class="text-lg font-bold text-[var(--color-ink)] tabular-nums" x-text="$store.cart.formatIDR($store.cart.total)"></span>
            </div>
            <a
                :href="$store.cart.waLink"
                target="_blank"
                rel="noopener noreferrer"
                class="flex w-full items-center justify-center gap-2 rounded-[var(--radius-xl)] bg-[var(--color-accent)] py-3 text-sm font-semibold text-white transition-all duration-[var(--dur-normal)] hover:opacity-90 active:scale-[0.98]"
            >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                Pesan via WhatsApp
            </a>
            <p class="mt-2 text-center text-[11px] text-[var(--color-ink-3)]">
                Pesanan dikonfirmasi lewat WhatsApp — bayar di tempat / transfer.
            </p>
        </div>
    </div>
</div>

{{-- Cart Toast — notifikasi singkat, auto-hide 2.5s --}}
<div
    x-data
    x-show="$store.cart.toast"
    x-cloak
    x-transition:enter="transition-all duration-[var(--dur-enter)]"
    x-transition:enter-start="opacity-0 translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition-all duration-[var(--dur-normal)]"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-4"
    class="fixed bottom-6 left-1/2 z-[110] -translate-x-1/2"
    role="status"
    aria-live="polite"
>
    <div class="flex items-center gap-2.5 rounded-[var(--radius-xl)] bg-[var(--color-ink)] px-5 py-3 text-sm font-medium text-white shadow-[var(--shadow-lg)]">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--color-success)" strokeWidth="2.5" strokeLinecap="round" aria-hidden="true">
            <polyline points="20 6 9 17 4 12" />
        </svg>
        <span x-text="$store.cart.toast"></span>
    </div>
</div>
