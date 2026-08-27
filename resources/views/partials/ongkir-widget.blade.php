{{-- Widget Cek Ongkir — Alpine.js + tema bawaan (CSS vars Donezo) --}}
<div x-data="ongkirWidget()" class="mt-6 rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white">
    <button @click="open = !open" class="flex w-full items-center justify-between px-5 py-4 text-left">
        <span class="text-sm font-semibold text-[var(--color-ink)]">Cek Ongkir</span>
        <svg :class="open && 'rotate-180'" class="h-4 w-4 text-[var(--color-ink-3)] transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div x-show="open" x-collapse x-cloak>
        <div class="border-t border-[var(--color-paper-3)] px-5 py-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="flex-1">
                    <label for="ongkir-area" class="mb-1 block text-xs font-medium text-[var(--color-ink-3)]">Kota / Kecamatan Tujuan</label>
                    <input id="ongkir-area" type="text" x-model="query" @input.debounce.400ms="searchAreas"
                           placeholder="cth: jakarta selatan"
                           autocomplete="off"
                           class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] bg-white px-3 py-2 text-sm text-[var(--color-ink)] placeholder:text-[var(--color-ink-3)] focus:border-[var(--color-accent)] focus:outline-none">
                    {{-- dropdown saran area --}}
                    <ul x-show="areas.length && !selected" x-cloak
                        class="relative z-10 mt-1 max-h-52 overflow-y-auto rounded-[var(--radius-md)] border border-[var(--color-paper-3)] bg-white shadow-lg">
                        <template x-for="a in areas" :key="a.code + a.label">
                            <li>
                                <button type="button" @click="pick(a)"
                                        class="w-full px-3 py-2 text-left text-sm text-[var(--color-ink-2)] hover:bg-[var(--color-accent-bg)]"
                                        x-text="a.label"></button>
                            </li>
                        </template>
                    </ul>
                </div>
                <div>
                    <label for="ongkir-weight" class="mb-1 block text-xs font-medium text-[var(--color-ink-3)]">Berat (gram)</label>
                    <input id="ongkir-weight" type="number" min="100" step="100" x-model.number="weight"
                           class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] bg-white px-3 py-2 text-sm text-[var(--color-ink)] sm:w-28 focus:border-[var(--color-accent)] focus:outline-none">
                </div>
                <button @click="fetchRates" :disabled="loading || !selected"
                        class="rounded-[var(--radius-md)] bg-[var(--color-accent)] px-5 py-2 text-sm font-semibold text-white transition-colors hover:bg-[var(--color-accent-hover)] disabled:opacity-50">
                    <span x-show="!loading">Cek Tarif</span>
                    <span x-show="loading" x-cloak>Mencari…</span>
                </button>
            </div>

            {{-- pesan error / info --}}
            <p x-show="error" x-cloak x-text="error" class="mt-3 text-sm text-[var(--color-danger)]"></p>

            {{-- hasil tarif --}}
            <div x-show="rates.length" x-cloak class="mt-4">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-[var(--color-ink-3)]"
                   x-text="'Tarif 1 kg ke ' + destinationLabel"></p>
                <ul class="divide-y divide-[var(--color-paper-3)]">
                    <template x-for="(r, i) in rates" :key="i">
                        <li class="flex items-center justify-between py-2">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-[var(--color-ink)]">
                                    <span x-text="r.courier"></span> <span class="font-normal text-[var(--color-ink-3)]" x-text="r.service"></span>
                                    <span x-show="r.cost === cheapest.cost"
                                          class="ml-2 rounded-full bg-[var(--color-accent-light)] px-2 py-0.5 text-[10px] font-bold text-[var(--color-accent)]">Termurah</span>
                                </p>
                                <p class="text-xs text-[var(--color-ink-3)]" x-text="r.description || r.etd"></p>
                            </div>
                            <p class="shrink-0 pl-4 text-sm font-bold text-[var(--color-accent)]" x-text="formatIDR(r.cost)"></p>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function ongkirWidget() {
    return {
        open: false,
        query: '',
        weight: 1000,
        selected: null,
        areas: [],
        rates: [],
        cheapest: null,
        loading: false,
        error: '',
        destinationLabel: '',
        async searchAreas() {
            if (this.query.trim().length < 3 || this.selected) return;
            try {
                const res = await fetch('/api/ongkir/areas?q=' + encodeURIComponent(this.query));
                const data = await res.json();
                this.areas = data.areas || [];
            } catch (e) { this.areas = []; }
        },
        pick(a) {
            this.selected = a;
            this.destinationLabel = a.label.split(',')[0].trim();
            this.query = a.label;
            this.areas = [];
            this.error = '';
        },
        async fetchRates() {
            if (!this.selected) return;
            this.loading = true; this.error = ''; this.rates = [];
            try {
                const res = await fetch(`/api/ongkir/rates?destination=${this.selected.code}&weight=${this.weight}`);
                const data = await res.json();
                if (!res.ok) { this.error = data.error || 'Gagal mengambil tarif.'; return; }
                this.rates = data.rates || [];
                this.cheapest = data.cheapest || null;
            } catch (e) {
                this.error = 'Gagal menghubungi layanan ongkir.';
            } finally {
                this.loading = false;
            }
        },
        formatIDR(n) { return 'Rp' + Number(n).toLocaleString('id-ID'); }
    }
}
</script>
