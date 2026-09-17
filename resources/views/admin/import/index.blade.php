@extends('admin.layouts.app')

@section('title', 'Import Katalog Produk — Multi-Tenant OMS')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-800">Engine Migrasi & Import Katalog</h1>
            <p class="text-sm text-slate-500">Migrasi produk instan dari berbagai sumber data ke toko UMKM dengan aman dan otomatis.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.import.history') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 shadow-xs hover:bg-slate-50 transition">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Riwayat Import
            </a>
        </div>
    </div>

    {{-- Import Wizard Container --}}
    <div class="rounded-2xl border border-slate-100 bg-white p-6 md:p-8 shadow-xs" x-data="{
        sourceType: 'mock',
        marketplaceUrl: '',
        duplicateStrategy: 'skip',
        validating: false,
        validationResult: null,
        validateSource() {
            if (this.sourceType !== 'marketplace') return;
            const url = (this.marketplaceUrl || '').trim();
            if (!url) return;
            this.validating = true;
            this.validationResult = null;
            fetch('{{ route('admin.import.validate') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    source_type: this.sourceType,
                    type: this.sourceType,
                    marketplace_url: url,
                    source_url: url,
                    source: url,
                    url: url
                })
            })
            .then(async res => {
                const data = await res.json();
                this.validating = false;
                if (!res.ok && !data.valid) {
                    this.validationResult = {
                        valid: false,
                        message: data.message || 'URL ditolak oleh sistem validasi.'
                    };
                } else {
                    this.validationResult = data;
                }
            })
            .catch(err => {
                this.validating = false;
                this.validationResult = { valid: false, message: 'Gagal melakukan validasi URL: ' + err.message };
            });
        }
    }">
        <form method="POST" action="{{ route('admin.import.preview') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- Target Store Selector --}}
            <div class="rounded-xl bg-slate-50 p-4 border border-slate-200/60">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    1. Toko UMKM Tujuan Import
                </label>
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                    <select name="target_store_id" class="w-full sm:w-80 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-800 shadow-2xs focus:border-emerald-600 focus:outline-none">
                        @foreach($stores as $st)
                            <option value="{{ $st->id }}" {{ $targetStoreId == $st->id ? 'selected' : '' }}>
                                {{ $st->name }} ({{ $st->slug }})
                            </option>
                        @endforeach
                    </select>
                    <span class="text-xs text-slate-500">
                        Produk yang diimport akan secara otomatis diisolasi untuk toko ini.
                    </span>
                </div>
            </div>

            {{-- Source Selection Cards --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">
                    2. Pilih Sumber / Metode Import
                </label>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- Mock Demo --}}
                    <label class="relative flex cursor-pointer flex-col justify-between rounded-xl border p-4 transition"
                        :class="sourceType === 'mock' ? 'border-emerald-600 bg-emerald-50/40 ring-2 ring-emerald-600/20' : 'border-slate-200 hover:border-slate-300'">
                        <input type="radio" name="source_type" value="mock" x-model="sourceType" class="sr-only">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 font-bold text-lg">
                                    🚀
                                </span>
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">47 Produk Siap</span>
                            </div>
                            <h3 class="mt-3 text-sm font-bold text-slate-800">Mock Data Pempek</h3>
                            <p class="mt-1 text-xs text-slate-500">
                                Generator 47 produk autentik Pempek, 8 kategori, 23 varian, dan 129 gambar.
                            </p>
                        </div>
                        <span class="mt-4 inline-block text-[11px] font-semibold text-emerald-700">Paling Praktis untuk Demo →</span>
                    </label>

                    {{-- CSV File --}}
                    <label class="relative flex cursor-pointer flex-col justify-between rounded-xl border p-4 transition"
                        :class="sourceType === 'csv' ? 'border-emerald-600 bg-emerald-50/40 ring-2 ring-emerald-600/20' : 'border-slate-200 hover:border-slate-300'">
                        <input type="radio" name="source_type" value="csv" x-model="sourceType" class="sr-only">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-700 font-bold text-lg">
                                    📊
                                </span>
                                <span class="rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-700">Spreadsheet</span>
                            </div>
                            <h3 class="mt-3 text-sm font-bold text-slate-800">File CSV</h3>
                            <p class="mt-1 text-xs text-slate-500">
                                Upload file spreadsheet CSV (nama, harga, kategori, stok, sku, deskripsi).
                            </p>
                        </div>
                        <span class="mt-4 inline-block text-[11px] font-semibold text-blue-700">Upload CSV File →</span>
                    </label>

                    {{-- JSON File --}}
                    <label class="relative flex cursor-pointer flex-col justify-between rounded-xl border p-4 transition"
                        :class="sourceType === 'json' ? 'border-emerald-600 bg-emerald-50/40 ring-2 ring-emerald-600/20' : 'border-slate-200 hover:border-slate-300'">
                        <input type="radio" name="source_type" value="json" x-model="sourceType" class="sr-only">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-700 font-bold text-lg">
                                    📦
                                </span>
                                <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-700">Struktur Lengkap</span>
                            </div>
                            <h3 class="mt-3 text-sm font-bold text-slate-800">File JSON</h3>
                            <p class="mt-1 text-xs text-slate-500">
                                Struktur JSON hierarkis mendukung varian & gambar bertingkat.
                            </p>
                        </div>
                        <span class="mt-4 inline-block text-[11px] font-semibold text-amber-700">Upload JSON File →</span>
                    </label>

                    {{-- Marketplace URL --}}
                    <label class="relative flex cursor-pointer flex-col justify-between rounded-xl border p-4 transition"
                        :class="sourceType === 'marketplace' ? 'border-emerald-600 bg-emerald-50/40 ring-2 ring-emerald-600/20' : 'border-slate-200 hover:border-slate-300'">
                        <input type="radio" name="source_type" value="marketplace" x-model="sourceType" class="sr-only">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 text-purple-700 font-bold text-lg">
                                    🌐
                                </span>
                                <span class="rounded-full bg-purple-50 px-2 py-0.5 text-[10px] font-bold text-purple-700">URL Scraper</span>
                            </div>
                            <h3 class="mt-3 text-sm font-bold text-slate-800">Marketplace Scraper</h3>
                            <p class="mt-1 text-xs text-slate-500">
                                Import via URL eksternal dengan proteksi anti-SSRF terverifikasi.
                            </p>
                        </div>
                        <span class="mt-4 inline-block text-[11px] font-semibold text-purple-700">URL Marketplace →</span>
                    </label>
                </div>
            </div>

            {{-- Dynamic Inputs Based on Source --}}
            <div class="rounded-xl border border-slate-200 p-5 bg-white">
                {{-- Mock Option Content --}}
                <div x-show="sourceType === 'mock'" class="space-y-2">
                    <h4 class="text-sm font-bold text-slate-800">Preset Mock Katalog Pempek</h4>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Data mock ini mensimulasikan katalog UMKM lengkap: 47 varian pempek (Kapal Selam, Lenjer, Adaan, Kulit, Keriting, Pistel, Panggang, dll.), 8 kategori, multi-satuan, dan foto produk siap pakai.
                    </p>
                </div>

                {{-- CSV Input --}}
                <div x-show="sourceType === 'csv'" x-cloak class="space-y-3">
                    <h4 class="text-sm font-bold text-slate-800">Pilih File CSV (.csv)</h4>
                    <input type="file" name="csv_file" accept=".csv,text/csv" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    <p class="text-[11px] text-slate-400">Header yang didukung: name, category, price, stock, sku, description, image, variants.</p>
                </div>

                {{-- JSON Input --}}
                <div x-show="sourceType === 'json'" x-cloak class="space-y-3">
                    <h4 class="text-sm font-bold text-slate-800">Pilih File JSON (.json)</h4>
                    <input type="file" name="json_file" accept=".json,application/json" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    <p class="text-[11px] text-slate-400">Mendukung format array objek atau objek dengan root key 'products'.</p>
                </div>

                {{-- Marketplace URL Input with SSRF validation --}}
                <div x-show="sourceType === 'marketplace'" x-cloak class="space-y-3">
                    <h4 class="text-sm font-bold text-slate-800">Marketplace / Website URL</h4>
                    <div class="flex gap-2">
                        <input
                            type="text"
                            name="marketplace_url"
                            x-model="marketplaceUrl"
                            placeholder="Contoh: https://shopee.co.id/wingsofficialshop atau shopee.co.id/toko"
                            class="flex-1 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm focus:border-emerald-600 focus:outline-none"
                        >
                        <button
                            type="button"
                            @click="validateSource()"
                            :disabled="!marketplaceUrl || validating"
                            class="rounded-xl border border-slate-200 bg-slate-100 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-200 disabled:opacity-50 transition"
                        >
                            <span x-show="!validating">Cek Keamanan URL</span>
                            <span x-show="validating">Memeriksa...</span>
                        </button>
                    </div>

                    <div x-show="validationResult" class="text-xs p-3 rounded-xl" :class="validationResult?.valid ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <p class="font-bold" x-text="validationResult?.valid ? '✓ URL Aman & Terverifikasi' : '⚠ URL Ditolak'"></p>
                                <p class="mt-0.5" x-text="validationResult?.message"></p>
                            </div>
                            <template x-if="validationResult?.valid">
                                <button
                                    type="submit"
                                    class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-xs font-bold text-white shadow-xs transition hover:opacity-90 cursor-pointer shrink-0"
                                    style="background-color: #0f4a2d !important; color: #ffffff !important;"
                                >
                                    <span>Lanjut ke Pratinjau</span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit Action --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-xl px-6 py-3 text-sm font-bold text-white shadow-md transition hover:opacity-95 cursor-pointer bg-[var(--color-accent,#0f4a2d)]"
                    style="background-color: #0f4a2d !important; color: #ffffff !important; min-height: 44px;"
                >
                    <span style="color: #ffffff !important; font-weight: 700;">Lanjut ke Pratinjau &amp; Cek Duplikat</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-white" style="color: #ffffff !important;">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
