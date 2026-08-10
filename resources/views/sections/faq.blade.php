@php
    $activeFaqs = $faqs->filter(fn($f) => $f->status === 'active')->sortBy('ord')->values();
@endphp

@if($activeFaqs->count() > 0)
<section id="faq" class="bg-[var(--color-paper-2)] py-16 sm:py-20 lg:py-24">
    <div class="mx-auto max-w-7xl px-5 sm:px-8">
        <div class="mx-auto max-w-3xl">
            <div class="mx-auto mb-10 text-center sm:mb-14">
                <h2 class="text-2xl font-bold tracking-tight sm:text-3xl lg:text-4xl" style="color: var(--color-ink)">Pertanyaan Umum</h2>
                <p class="mx-auto mt-3 max-w-2xl text-base text-[var(--color-ink-2)] sm:text-lg">Temukan jawaban untuk pertanyaan yang sering diajukan.</p>
            </div>

            <div class="rounded-[var(--radius-xl)] bg-white p-2 sm:p-4" x-data="{ open: null }">
                @foreach($activeFaqs as $index => $faq)
                <div class="border-b border-[var(--color-paper-3)] last:border-b-0">
                    <button
                        @click="open = open === {{ $index }} ? null : {{ $index }}"
                        class="flex w-full items-center justify-between py-5 text-left transition-colors duration-[var(--dur-normal)] hover:text-[var(--color-accent)] focus-visible:outline-2 focus-visible:outline-offset-2"
                        :aria-expanded="open === {{ $index }}"
                    >
                        <span class="pr-4 text-base font-semibold text-[var(--color-ink)] sm:text-lg">{{ $faq->question }}</span>
                        <span class="shrink-0 transition-transform duration-[var(--dur-slow)]" :style="open === {{ $index }} ? 'transform: rotate(45deg)' : ''" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10 4V16M4 10H16" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
                            </svg>
                        </span>
                    </button>
                    <div
                        x-show="open === {{ $index }}"
                        x-collapse
                        class="text-[var(--color-ink-2)] leading-relaxed"
                    >
                        <div class="pb-5">{{ $faq->answer }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif
