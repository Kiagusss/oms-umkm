{{-- Cara Pemesanan — 4 langkah vertikal --}}
<section class="bg-[var(--color-paper-2)] py-16 sm:py-20 lg:py-24">
    <div class="mx-auto max-w-7xl px-5 sm:px-8">
        <div class="mx-auto mb-10 text-center sm:mb-14">
            <h2 class="text-2xl font-bold tracking-tight sm:text-3xl lg:text-4xl" style="color: var(--color-ink)">Cara Pemesanan</h2>
            <p class="mx-auto mt-3 max-w-2xl text-base text-[var(--color-ink-2)] sm:text-lg">Pesan pempek favorit Anda dalam 4 langkah mudah.</p>
        </div>

        <div class="mx-auto max-w-3xl">
            <div class="relative">
                <div class="absolute left-6 top-0 bottom-0 w-px bg-[var(--color-paper-3)] sm:left-8" aria-hidden="true"></div>

                <div class="space-y-8">
                    {{-- 1. Pilih Menu --}}
                    <div class="relative flex gap-5 sm:gap-6">
                        <div class="relative z-10 flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[var(--color-accent)] text-white shadow-[var(--shadow-sm)] sm:h-16 sm:w-16">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                                <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20" />
                            </svg>
                        </div>
                        <div class="flex-1 rounded-[var(--radius-xl)] bg-white p-5 shadow-[var(--shadow-sm)] sm:p-6">
                            <span class="text-xs font-bold tabular-nums text-[var(--color-accent)]">LANGKAH 1</span>
                            <h3 class="mt-1 text-base font-semibold text-[var(--color-ink)] sm:text-lg">Pilih Menu</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-[var(--color-ink-2)]">Pilih pempek dan paket favorit Anda dari daftar menu kami.</p>
                        </div>
                    </div>

                    {{-- 2. WhatsApp --}}
                    <div class="relative flex gap-5 sm:gap-6">
                        <div class="relative z-10 flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[var(--color-accent)] text-white shadow-[var(--shadow-sm)] sm:h-16 sm:w-16">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                            </svg>
                        </div>
                        <div class="flex-1 rounded-[var(--radius-xl)] bg-white p-5 shadow-[var(--shadow-sm)] sm:p-6">
                            <span class="text-xs font-bold tabular-nums text-[var(--color-accent)]">LANGKAH 2</span>
                            <h3 class="mt-1 text-base font-semibold text-[var(--color-ink)] sm:text-lg">Hubungi WhatsApp</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-[var(--color-ink-2)]">Kirim pesanan Anda melalui WhatsApp dengan format yang mudah.</p>
                        </div>
                    </div>

                    {{-- 3. Pembayaran --}}
                    <div class="relative flex gap-5 sm:gap-6">
                        <div class="relative z-10 flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[var(--color-accent)] text-white shadow-[var(--shadow-sm)] sm:h-16 sm:w-16">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                                <rect x="1" y="4" width="22" height="16" rx="2" ry="2" />
                                <line x1="1" y1="10" x2="23" y2="10" />
                            </svg>
                        </div>
                        <div class="flex-1 rounded-[var(--radius-xl)] bg-white p-5 shadow-[var(--shadow-sm)] sm:p-6">
                            <span class="text-xs font-bold tabular-nums text-[var(--color-accent)]">LANGKAH 3</span>
                            <h3 class="mt-1 text-base font-semibold text-[var(--color-ink)] sm:text-lg">Pembayaran</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-[var(--color-ink-2)]">Lakukan pembayaran via transfer bank atau e-wallet.</p>
                        </div>
                    </div>

                    {{-- 4. Pengiriman --}}
                    <div class="relative flex gap-5 sm:gap-6">
                        <div class="relative z-10 flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[var(--color-accent)] text-white shadow-[var(--shadow-sm)] sm:h-16 sm:w-16">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                                <rect x="1" y="3" width="15" height="13" rx="1" />
                                <path d="M16 8h4l3 3v5h-7V8z" />
                                <circle cx="5.5" cy="18.5" r="2.5" />
                                <circle cx="18.5" cy="18.5" r="2.5" />
                            </svg>
                        </div>
                        <div class="flex-1 rounded-[var(--radius-xl)] bg-white p-5 shadow-[var(--shadow-sm)] sm:p-6">
                            <span class="text-xs font-bold tabular-nums text-[var(--color-accent)]">LANGKAH 4</span>
                            <h3 class="mt-1 text-base font-semibold text-[var(--color-ink)] sm:text-lg">Pengiriman</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-[var(--color-ink-2)]">Pesanan diproses dan dikirim langsung ke alamat Anda.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
