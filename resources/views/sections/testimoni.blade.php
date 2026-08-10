@php
    $active = $testimonials->filter(fn($t) => $t->status === 'active')->values();
    $itemsJson = json_encode($active->map(fn($t) => [
        'name' => $t->name,
        'initial' => substr($t->name, 0, 1),
        'rating' => (int) $t->rating,
        'comment' => $t->comment,
    ]));
@endphp

@if($active->count() > 0)
<section id="testimoni" class="bg-[var(--color-paper)] py-16 sm:py-20 lg:py-24">
    <div class="mx-auto max-w-7xl px-5 sm:px-8">
        <div class="mb-10 sm:mb-14">
            <h2 class="text-2xl font-bold tracking-tight sm:text-3xl lg:text-4xl" style="color: var(--color-ink)">Apa Kata Mereka</h2>
            <p class="mt-3 max-w-2xl text-base text-[var(--color-ink-2)] sm:text-lg">Testimoni dari pelanggan setia kami.</p>
        </div>

        <div class="relative mx-auto max-w-2xl" x-data="testimoniData({{ $itemsJson }})">
            <div class="rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white p-6 sm:p-8">
                <svg class="mb-4 text-[var(--color-accent-light)]" width="40" height="40" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10H14.017zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151C7.563 6.068 6 8.789 6 11h4v10H0z" />
                </svg>

                <p class="text-base leading-relaxed text-[var(--color-ink-2)] sm:text-lg" x-text="items[current].comment"></p>

                <div class="mt-6 flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[var(--color-accent-light)] text-lg font-bold text-[var(--color-accent)]" x-text="items[current].initial"></div>
                    <div>
                        <p class="font-semibold text-[var(--color-ink)]" x-text="items[current].name"></p>
                        <div class="mt-0.5 flex gap-0.5" role="img" :aria-label="'Rating ' + items[current].rating + ' dari 5 bintang'">
                            <template x-for="i in 5" :key="i">
                                <svg width="14" height="14" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M10 1.5l2.47 5.01 5.53.8-4 3.9.94 5.49L10 14.26 5.06 16.7 6 11.21l-4-3.9 5.53-.8L10 1.5z" :fill="i <= items[current].rating ? 'var(--color-accent)' : 'var(--color-paper-3)'" />
                                </svg>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-center gap-4">
                <button @click="current = (current - 1 + total) % total" class="flex h-10 w-10 items-center justify-center rounded-full border border-[var(--color-paper-3)] text-[var(--color-ink-2)] transition-all duration-[var(--dur-normal)] hover:border-[var(--color-accent)] hover:text-[var(--color-accent)]" aria-label="Testimoni sebelumnya">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" aria-hidden="true"><polyline points="15 18 9 12 15 6" /></svg>
                </button>

                <div class="flex gap-2">
                    <template x-for="(t, i) in items" :key="i">
                        <button @click="current = i" class="h-2 rounded-full transition-all duration-[var(--dur-normal)]" :class="current === i ? 'w-6 bg-[var(--color-accent)]' : 'w-2 bg-[var(--color-paper-3)] hover:bg-[var(--color-ink-3)]'" :aria-label="'Testimoni ' + (i + 1)"></button>
                    </template>
                </div>

                <button @click="current = (current + 1) % total" class="flex h-10 w-10 items-center justify-center rounded-full border border-[var(--color-paper-3)] text-[var(--color-ink-2)] transition-all duration-[var(--dur-normal)] hover:border-[var(--color-accent)] hover:text-[var(--color-accent)]" aria-label="Testimoni berikutnya">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" aria-hidden="true"><polyline points="9 18 15 12 9 6" /></svg>
                </button>
            </div>
        </div>
    </div>
</section>

<script>
function testimoniData(items) {
    return {
        current: 0,
        items: items,
        get total() { return this.items.length; },
    };
}
</script>
@endif