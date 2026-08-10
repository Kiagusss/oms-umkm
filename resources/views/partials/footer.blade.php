<footer class="border-t border-[var(--color-paper-3)] bg-[var(--color-paper)]" role="contentinfo">
    <div class="mx-auto max-w-7xl px-5 sm:px-8">
        <div class="grid gap-10 py-12 sm:py-16 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Brand column --}}
            <div class="sm:col-span-2 lg:col-span-1">
                <a href="/" class="inline-block text-2xl font-bold tracking-tight text-[var(--color-ink)]">
                    Pempek<span class="text-[var(--color-accent)]">.</span>
                </a>
                <p class="mt-3 max-w-xs text-sm leading-relaxed text-[var(--color-ink-2)]">
                    Pempek asli Palembang dibuat fresh setiap hari dari ikan tenggiri pilihan dengan resep turun-temurun.
                </p>
                <div class="mt-5 flex gap-3">
                    <a href="https://instagram.com/pempekpalembang" target="_blank" rel="noopener noreferrer" aria-label="Instagram" class="flex h-9 w-9 items-center justify-center rounded-[var(--radius-lg)] bg-[var(--color-paper-2)] text-[var(--color-ink-2)] transition-all duration-[var(--dur-normal)] hover:bg-[var(--color-accent)] hover:text-white">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                        </svg>
                    </a>
                    <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp" class="flex h-9 w-9 items-center justify-center rounded-[var(--radius-lg)] bg-[var(--color-paper-2)] text-[var(--color-ink-2)] transition-all duration-[var(--dur-normal)] hover:bg-[var(--color-accent)] hover:text-white">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/>
                        </svg>
                    </a>
                    <a href="mailto:info@pempekpalembang.com" aria-label="Email" class="flex h-9 w-9 items-center justify-center rounded-[var(--radius-lg)] bg-[var(--color-paper-2)] text-[var(--color-ink-2)] transition-all duration-[var(--dur-normal)] hover:bg-[var(--color-accent)] hover:text-white">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                            <rect x="2" y="4" width="20" height="16" rx="2" />
                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Quick links --}}
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-[var(--color-ink)]">Navigasi</h3>
                <ul class="mt-4 space-y-2.5">
                    <li><a href="#beranda" class="text-sm text-[var(--color-ink-2)] transition-colors duration-[var(--dur-normal)] hover:text-[var(--color-accent)]">Beranda</a></li>
                    <li><a href="#produk" class="text-sm text-[var(--color-ink-2)] transition-colors duration-[var(--dur-normal)] hover:text-[var(--color-accent)]">Menu</a></li>
                    <li><a href="#tentang" class="text-sm text-[var(--color-ink-2)] transition-colors duration-[var(--dur-normal)] hover:text-[var(--color-accent)]">Tentang</a></li>
                    <li><a href="#testimoni" class="text-sm text-[var(--color-ink-2)] transition-colors duration-[var(--dur-normal)] hover:text-[var(--color-accent)]">Testimoni</a></li>
                    <li><a href="#faq" class="text-sm text-[var(--color-ink-2)] transition-colors duration-[var(--dur-normal)] hover:text-[var(--color-accent)]">FAQ</a></li>
                    <li><a href="#artikel" class="text-sm text-[var(--color-ink-2)] transition-colors duration-[var(--dur-normal)] hover:text-[var(--color-accent)]">Artikel</a></li>
                </ul>
            </div>

            {{-- Contact --}}
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-[var(--color-ink)]">Kontak</h3>
                <ul class="mt-4 space-y-3 text-sm text-[var(--color-ink-2)]">
                    <li class="flex gap-2">
                        <svg class="mt-0.5 shrink-0" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" aria-hidden="true">
                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                            <circle cx="12" cy="10" r="3" />
                        </svg>
                        <span>Jl. Merdeka No. 123, Palembang, Sumatera Selatan 30129</span>
                    </li>
                    <li class="flex gap-2">
                        <svg class="mt-0.5 shrink-0" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                        <span>Senin - Minggu, 08.00 - 21.00 WIB</span>
                    </li>
                    <li class="flex gap-2">
                        <svg class="mt-0.5 shrink-0" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/>
                        </svg>
                        <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener noreferrer" class="transition-colors hover:text-[var(--color-accent)]">
                            +62 812-3456-7890
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Maps --}}
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-[var(--color-ink)]">Lokasi</h3>
                <div class="mt-4 overflow-hidden rounded-[var(--radius-xl)] border border-[var(--color-paper-3)]">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3984.3!2d104.7!3d-2.9!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMsKwNTQnMDAuMCJTIDEwNMKwNDInMDAuMCJF!5e0!3m2!1sid!2sid!4v1"
                        width="100%"
                        height="160"
                        style="border: 0;"
                        allowfullscreen
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        title="Lokasi Pempek Palembang"
                    ></iframe>
                </div>
            </div>
        </div>

        <div class="border-t border-[var(--color-paper-3)] py-5 text-center">
            <p class="text-xs text-[var(--color-ink-3)]">
                &copy; {{ date('Y') }} Pempek Palembang. Seluruh hak cipta dilindungi.
            </p>
        </div>
    </div>
</footer>
