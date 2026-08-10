{{-- Produk — grid produk aktif dengan tombol Pesan (Alpine event bus) --}}
<section id="produk" class="bg-[var(--color-paper-2)] py-16 sm:py-20 lg:py-24">
    <div class="mx-auto max-w-7xl px-5 sm:px-8">
        <div class="mb-10 sm:mb-14">
            <h2 class="text-2xl font-bold tracking-tight sm:text-3xl lg:text-4xl" style="color: var(--color-ink)">Menu Pempek Kami</h2>
            <p class="mt-3 max-w-2xl text-base text-[var(--color-ink-2)] sm:text-lg">Pilihan pempek segar dengan berbagai varian, dibuat dari ikan tenggiri pilihan.</p>
        </div>

        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($products->where('status', 'active') as $product)
            <article class="group overflow-hidden rounded-[var(--radius-xl)] bg-white transition-all duration-[var(--dur-slow)] hover:shadow-[var(--shadow-md)] hover:-translate-y-0.5">
                {{-- Image --}}
                <div class="relative aspect-[4/3] overflow-hidden bg-[var(--color-paper-2)]">
                    <img
                        src="{{ asset($product->thumbnail) }}"
                        alt="{{ $product->name }}"
                        class="h-full w-full object-cover transition-transform duration-[var(--dur-slow)] group-hover:scale-105"
                    >
                    @if($product->is_best_seller)
                    <div class="absolute top-3 left-3">
                        <span class="inline-flex items-center rounded-[var(--radius-md)] bg-[var(--color-accent-light)] px-2.5 py-0.5 text-xs font-semibold text-[var(--color-accent)]">Best Seller</span>
                    </div>
                    @endif
                </div>

                {{-- Content --}}
                <div class="p-5">
                    <h3 class="text-base font-semibold text-[var(--color-ink)]">{{ $product->name }}</h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-[var(--color-ink-2)] line-clamp-2">{{ $product->short_description }}</p>

                    <div class="mt-4 flex items-center justify-between">
                        <div class="flex items-baseline gap-2">
                            <span class="text-lg font-bold text-[var(--color-accent)]">Rp{{ number_format($product->price, 0, ',', '.') }}</span>
                            @if($product->price_strikethrough)
                            <span class="text-sm text-[var(--color-ink-3)] line-through">Rp{{ number_format($product->price_strikethrough, 0, ',', '.') }}</span>
                            @endif
                        </div>
                    </div>

                    <button
                        @click="$store.cart.addItem({ id: '{{ $product->id }}', name: {{ json_encode($product->name) }}, price: {{ $product->price }}, thumbnail: '{{ asset($product->thumbnail) }}', stock: {{ $product->stock }} })"
                        {{ $product->stock <= 0 ? 'disabled' : '' }}
                        class="mt-4 flex w-full items-center justify-center gap-2 rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] py-2.5 text-sm font-semibold text-[var(--color-ink)] transition-all duration-[var(--dur-normal)] hover:border-[var(--color-accent)] hover:text-[var(--color-accent)] active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        {{ $product->stock <= 0 ? 'Stok Habis' : 'Pesan' }}
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" aria-hidden="true">
                            <path d="M7 17L17 7M17 7H7M17 7v10" />
                        </svg>
                    </button>
                </div>
            </article>
            @endforeach
        </div>
    </div>
</section>
