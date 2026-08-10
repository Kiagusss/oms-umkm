{{-- Tentang Kami --}}
<section id="tentang" class="bg-[var(--color-paper)] py-16 sm:py-20 lg:py-24">
    <div class="mx-auto max-w-7xl px-5 sm:px-8">
        <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
            {{-- Image — left --}}
            <div class="relative">
                <div class="aspect-[4/3] overflow-hidden rounded-[var(--radius-2xl)] bg-[var(--color-paper-2)]">
                    <img src="{{ asset('images/hero-pempek.png') }}" alt="Proses pembuatan pempek asli Palembang" class="h-full w-full object-cover">
                </div>
                <div class="absolute -right-3 top-6 bottom-6 hidden w-1 rounded-[var(--radius-full)] bg-[var(--color-accent)] lg:block" aria-hidden="true"></div>
            </div>

            {{-- Text — right --}}
            <div>
                <div class="mb-10 sm:mb-14">
                    <h2 class="text-2xl font-bold tracking-tight sm:text-3xl lg:text-4xl" style="color: var(--color-ink)">Warisan Rasa dari Palembang</h2>
                    <p class="mt-3 max-w-2xl text-base text-[var(--color-ink-2)] sm:text-lg">Lebih dari sekadar makanan — ini adalah warisan kuliner yang kami jaga dengan penuh dedikasi.</p>
                </div>

                <div class="space-y-5 leading-relaxed text-[var(--color-ink-2)]">
                    <p>
                        Berawal dari resep keluarga yang diwariskan selama tiga generasi, kami memulai usaha pempek ini dengan satu keyakinan sederhana: bahan terbaik menghasilkan rasa terbaik.
                    </p>
                    <p>
                        Setiap hari, kami memilih ikan tenggiri segar langsung dari pasar ikan Palembang. Tanpa pengawet, tanpa pewarna buatan — hanya bahan alami yang kami percaya akan memberikan cita rasa autentik.
                    </p>
                    <p>
                        Proses pembuatan kami mengikuti cara tradisional: ikan digiling halus, dicampur dengan sagu pilihan, lalu dibentuk dan dimasak dengan teknik yang telah teruji puluhan tahun.
                    </p>
                </div>

                {{-- Values --}}
                <div class="mt-8 grid grid-cols-2 gap-4">
                    <div class="rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] p-4">
                        <p class="text-lg font-bold text-[var(--color-accent)]">3 Generasi</p>
                        <p class="text-sm text-[var(--color-ink-3)]">Resep Turun-Temurun</p>
                    </div>
                    <div class="rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] p-4">
                        <p class="text-lg font-bold text-[var(--color-accent)]">100%</p>
                        <p class="text-sm text-[var(--color-ink-3)]">Bahan Alami</p>
                    </div>
                    <div class="rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] p-4">
                        <p class="text-lg font-bold text-[var(--color-accent)]">Fresh</p>
                        <p class="text-sm text-[var(--color-ink-3)]">Produksi Harian</p>
                    </div>
                    <div class="rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] p-4">
                        <p class="text-lg font-bold text-[var(--color-accent)]">Prioritas</p>
                        <p class="text-sm text-[var(--color-ink-3)]">Kepuasan Pelanggan</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
