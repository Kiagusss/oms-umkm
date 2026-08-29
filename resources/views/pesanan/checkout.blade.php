@extends('layouts.app')

@section('title', 'Data Pemesanan — ' . config('app.name', 'Pempek Palembang'))

@section('content')
<div class="mx-auto max-w-xl px-4 py-12 sm:px-6 lg:px-8" x-data="checkoutForm()">
    <h1 class="text-2xl font-bold text-[var(--color-ink)]">Data Pemesanan</h1>
    <p class="mt-1 text-sm text-[var(--color-ink-3)]">Isi datamu, lalu selesaikan pembayaran QRIS.</p>

    @if(session('error'))
        <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Ringkasan --}}
    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Ringkasan Pesanan</h2>
        <template x-for="i in items" :key="i.id">
            <div class="flex items-center justify-between py-1.5 text-sm">
                <span class="text-slate-600" x-text="i.name + ' x' + i.quantity"></span>
                <span class="font-medium text-slate-700 tabular-nums" x-text="formatIDR(i.price * i.quantity)"></span>
            </div>
        </template>
        <div class="mt-2 flex items-center justify-between border-t border-slate-100 pt-2 text-sm">
            <span class="text-slate-500">Subtotal</span>
            <span class="font-semibold text-slate-700 tabular-nums" x-text="formatIDR(subtotal)"></span>
        </div>
        <div class="flex items-center justify-between text-sm" x-show="shippingCost > 0" x-cloak>
            <span class="text-slate-500">Ongkir <span x-text="shippingLabel ? '· ' + shippingLabel : ''"></span></span>
            <span class="font-semibold text-slate-700 tabular-nums" x-text="formatIDR(shippingCost)"></span>
        </div>
        <div class="mt-2 flex items-center justify-between border-t border-slate-100 pt-2">
            <span class="text-sm font-bold text-slate-800">Total</span>
            <span class="text-lg font-bold text-[var(--color-accent)] tabular-nums" x-text="formatIDR(subtotal + shippingCost)"></span>
        </div>
    </div>

    <form method="POST" action="{{ route('checkout') }}" class="mt-6 space-y-4" @submit.prevent="submitForm($el)">
        @csrf
        <template x-for="(i, idx) in items" :key="i.id">
            <span>
                <input type="hidden" :name="'items[' + idx + '][id]'" :value="i.id">
                <input type="hidden" :name="'items[' + idx + '][quantity]'" :value="i.quantity">
            </span>
        </template>
        <input type="hidden" name="payment_method" value="QRIS">
        <template x-if="shippingPayload">
            <span>
                <input type="hidden" name="shipping[destination]" :value="shippingPayload.destination">
                <input type="hidden" name="shipping[weight]" :value="shippingPayload.weight">
                <input type="hidden" name="shipping[courier]" :value="shippingPayload.courier">
                <input type="hidden" name="shipping[service]" :value="shippingPayload.service">
            </span>
        </template>

        <div>
            <label for="f-name" class="mb-1 block text-sm font-medium text-slate-700">Nama lengkap <span class="text-red-500">*</span></label>
            <input id="f-name" name="customer_name" type="text" required maxlength="255" value="{{ old('customer_name') }}"
                class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none transition focus:border-[var(--color-accent)] focus:ring-2 focus:ring-[var(--color-accent)]/20"
                placeholder="cth: Budi Santoso">
        </div>

        <div>
            <label for="f-wa" class="mb-1 block text-sm font-medium text-slate-700">Nomor HP / WhatsApp <span class="text-red-500">*</span></label>
            <input id="f-wa" name="customer_whatsapp" type="tel" required maxlength="50" value="{{ old('customer_whatsapp') }}"
                class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none transition focus:border-[var(--color-accent)] focus:ring-2 focus:ring-[var(--color-accent)]/20"
                placeholder="cth: 0812-3456-7890">
        </div>

        <div>
            <label for="f-addr" class="mb-1 block text-sm font-medium text-slate-700">Alamat pengiriman <span class="text-red-500">*</span></label>
            <textarea id="f-addr" name="address" required maxlength="1000" rows="3" value="{{ old('address') }}"
                class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none transition focus:border-[var(--color-accent)] focus:ring-2 focus:ring-[var(--color-accent)]/20"
                placeholder="Jalan, kelurahan, kecamatan, kota, kode pos">{{ old('address') }}</textarea>
        </div>

        {{-- Ongkir opsional --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <label class="flex cursor-pointer items-center gap-2">
                <input type="checkbox" x-model="useShipping" class="h-4 w-4 accent-[var(--color-accent)]">
                <span class="text-sm font-semibold text-slate-700">Hitung ongkir</span>
                <span class="text-xs text-slate-400">(opsional — bisa juga nego via WA)</span>
            </label>

            <div x-show="useShipping" x-cloak class="mt-4 space-y-3">
                <div class="relative">
                    <label class="mb-1 block text-xs font-medium text-slate-500">Kota / kecamatan tujuan</label>
                    <input type="text" x-model="areaQuery" @input.debounce.400ms="searchAreas"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none transition focus:border-[var(--color-accent)]"
                        placeholder="cth: Jakarta Selatan" autocomplete="off">
                    <ul x-show="areas.length && !selectedArea" x-cloak class="absolute z-10 mt-1 max-h-48 w-full overflow-auto rounded-xl border border-slate-200 bg-white shadow-lg">
                        <template x-for="a in areas" :key="a.cityName + a.type">
                            <li><button type="button" @click="pickArea(a)" class="block w-full px-3 py-2 text-left text-sm hover:bg-[var(--color-accent-light)]"
                                x-text="a.label"></button></li>
                        </template>
                    </ul>
                    <p x-show="selectedArea" class="mt-1 text-xs font-medium text-[var(--color-accent)]" x-text="'Dipilih: ' + (selectedArea ? selectedArea.label : '')"></p>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">Berat (gram)</label>
                    <input type="number" x-model.number="weight" min="100" step="100"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none transition focus:border-[var(--color-accent)]">
                </div>

                <button type="button" @click="fetchRates" :disabled="loading || !selectedArea"
                    class="rounded-xl border-2 border-[var(--color-accent)] px-4 py-2 text-xs font-semibold text-[var(--color-accent)] transition hover:bg-[var(--color-accent-light)] disabled:opacity-40">
                    <span x-show="!loading">Cek Ongkir</span>
                    <span x-show="loading" x-cloak>Mencari…</span>
                </button>

                <p x-show="error" x-cloak class="text-xs text-red-600" x-text="error"></p>

                <div x-show="rates.length" x-cloak class="space-y-2">
                    <template x-for="(r, i) in rates" :key="i">
                        <label class="flex cursor-pointer items-center justify-between rounded-xl border px-3 py-2 text-sm transition"
                            :class="picked && picked.courier === r.courier && picked.service === r.service ? 'border-[var(--color-accent)] bg-[var(--color-accent-light)]' : 'border-slate-200 hover:border-slate-300'">
                            <span class="flex items-center gap-2">
                                <input type="radio" name="courier_pick" value="" x-model="pickedKey" :value="r.courier + '|' + r.service" class="accent-[var(--color-accent)]">
                                <span x-text="r.courier + ' — ' + r.service" class="text-slate-700"></span>
                            </span>
                            <span class="font-semibold tabular-nums text-slate-700" x-text="formatIDR(r.cost)"></span>
                        </label>
                    </template>
                </div>
            </div>
        </div>

        <div>
            <label for="f-notes" class="mb-1 block text-sm font-medium text-slate-700">Catatan <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
            <input id="f-notes" name="notes" type="text" maxlength="500" value="{{ old('notes') }}"
                class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none transition focus:border-[var(--color-accent)] focus:ring-2 focus:ring-[var(--color-accent)]/20"
                placeholder="cth: jangan pedas">
        </div>

        <button type="submit" :disabled="submitting"
            class="flex w-full items-center justify-center gap-2 rounded-[var(--radius-xl)] bg-[var(--color-accent)] py-3.5 text-sm font-bold text-white transition-all hover:opacity-90 active:scale-[0.98] disabled:opacity-50">
            <span x-show="!submitting">Lanjut ke Pembayaran QRIS</span>
            <span x-show="submitting" x-cloak>Memproses…</span>
        </button>
        <p class="text-center text-xs text-slate-400">Kamu akan diarahkan ke halaman QRIS setelah menekan tombol ini.</p>
    </form>
</div>

<script>
function checkoutForm() {
    return {
        items: [],
        useShipping: {{ old('shipping') ? 'true' : 'false' }},
        areaQuery: '', areas: [], selectedArea: null,
        weight: {{ old('shipping.weight', 1000) }},
        rates: [], pickedKey: '', loading: false, error: '',
        submitting: false,
        init() {
            this.items = Alpine.store('cart').items.map(i => ({ id: i.id, name: i.name, price: i.price, quantity: i.quantity }));
            if (!this.items.length) location.href = '/';
        },
        get subtotal() { return this.items.reduce((s, i) => s + i.price * i.quantity, 0); },
        get picked() {
            if (!this.pickedKey) return null;
            const [c, s] = this.pickedKey.split('|');
            return this.rates.find(r => r.courier === c && r.service === s) || null;
        },
        get shippingCost() { return this.useShipping && this.picked ? this.picked.cost : 0; },
        get shippingLabel() { return this.picked ? this.picked.courier + ' - ' + this.picked.service : ''; },
        get shippingPayload() {
            if (!this.useShipping || !this.picked || !this.selectedArea) return null;
            return { destination: this.selectedArea.code, weight: this.weight, courier: this.picked.courier, service: this.picked.service };
        },
        formatIDR(n) { return 'Rp' + (n || 0).toLocaleString('id-ID'); },
        async searchAreas() {
            if (this.areaQuery.trim().length < 3 || this.selectedArea) { this.areas = []; return; }
            try {
                const res = await fetch('/api/ongkir/areas?q=' + encodeURIComponent(this.areaQuery));
                const data = await res.json();
                this.areas = data.areas || [];
            } catch (e) { this.areas = []; }
        },
        pickArea(a) { this.selectedArea = a; this.areas = []; this.areaQuery = a.label; this.rates = []; this.pickedKey = ''; },
        async fetchRates() {
            if (!this.selectedArea) return;
            this.loading = true; this.error = ''; this.rates = []; this.pickedKey = '';
            try {
                const res = await fetch(`/api/ongkir/rates?destination=${this.selectedArea.code}&weight=${this.weight}`);
                const data = await res.json();
                if (!res.ok) throw new Error(data.error || 'Gagal');
                this.rates = data.rates || [];
                if (!this.rates.length) this.error = 'Tidak ada tarif untuk tujuan ini.';
            } catch (e) { this.error = 'Gagal mengambil ongkir: ' + e.message; }
            this.loading = false;
        },
        submitForm(form) {
            if (this.useShipping && !this.picked) { alert('Pilih kurir dulu, atau matikan centang "Hitung ongkir".'); return; }
            this.submitting = true;
            try { localStorage.removeItem('pempek_cart_v2'); } catch (e) {}
            form.submit();
        }
    };
}
</script>
@endsection