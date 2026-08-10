{{-- Paket Hemat — kartu paket dengan badge, items list, tombol ke keranjang --}}
<section class="bg-[var(--color-paper)] py-16 sm:py-20 lg:py-24">
    <div class="mx-auto max-w-7xl px-5 sm:px-8">
        <div class="mb-10 sm:mb-14">
            <h2 class="text-2xl font-bold tracking-tight sm:text-3xl lg:text-4xl" style="color: var(--color-ink)">Paket Hemat</h2>
            <p class="mt-3 max-w-2xl text-base text-[var(--color-ink-2)] sm:text-lg">Pilih paket yang sesuai kebutuhan Anda — lebih hemat dan lebih praktis.</p>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($packages as $index => $pkg)
            @php
                $isFeatured = $index === 0;
                $items = is_array($pkg->items) ? $pkg->items : [];
            @endphp
            <div
                class="relative flex flex-col rounded-[var(--radius-xl)] border p-6 transition-all duration-[var(--dur-slow)] hover:shadow-[var(--shadow-md)] hover:-translate-y-0.5 {{ $isFeatured ? 'border-[var(--color-accent)] bg-[var(--color-accent-bg)]' : 'border-[var(--color-paper-3)] bg-white' }}"
            >
                @if($pkg->badge)
                <div class="absolute -top-3 left-5">
                    <span class="inline-flex items-center rounded-[var(--radius-md)] bg-[var(--color-accent-light)] px-2.5 py-0.5 text-xs font-semibold {{ $isFeatured ? 'text-[var(--color-accent)]' : 'text-[var(--color-ink-2)] bg-[var(--color-paper-2)]' }}">{{ $pkg->badge }}</span>
                </div>
                @endif

                <h3 class="text-xl font-bold text-[var(--color-ink)]">{{ $pkg->name }}</h3>
                <p class="mt-2 text-sm leading-relaxed text-[var(--color-ink-2)]">{{ $pkg->description }}</p>

                {{-- Items list --}}
                <ul class="mt-5 flex-1 space-y-2">
                    @foreach($items as $item)
                    <li class="flex items-center gap-2 text-sm text-[var(--color-ink-2)]">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--color-accent)" strokeWidth="2.5" strokeLinecap="round" aria-hidden="true">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                        {{ $item['quantity'] }}x {{ $item['name'] }}
                    </li>
                    @endforeach
                </ul>

                {{-- Price --}}
                <div class="mt-6 flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-[var(--color-ink)]">Rp{{ number_format($pkg->price, 0, ',', '.') }}</span>
                    @if($pkg->original_price)
                    <span class="text-sm text-[var(--color-ink-3)] line-through">Rp{{ number_format($pkg->original_price, 0, ',', '.') }}</span>
                    @endif
                </div>

                <button
                    @click="$store.cart.addItem({ id: 'pkg-{{ $pkg->id }}', name: {{ json_encode($pkg->name) }}, price: {{ $pkg->price }} })"
                    class="mt-5 flex w-full items-center justify-center gap-2 rounded-[var(--radius-xl)] py-3 text-sm font-semibold transition-all duration-[var(--dur-normal)] active:scale-[0.98] {{ $isFeatured ? 'bg-[var(--color-accent)] text-white hover:bg-[var(--color-accent-hover)]' : 'border border-[var(--color-paper-3)] text-[var(--color-ink)] hover:border-[var(--color-accent)] hover:text-[var(--color-accent)]' }}"
                >
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                        <circle cx="8" cy="21" r="1" />
                        <circle cx="19" cy="21" r="1" />
                        <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12" />
                    </svg>
                    Masukkan ke Keranjang
                </button>
            </div>
            @endforeach
        </div>
    </div>
</section>
