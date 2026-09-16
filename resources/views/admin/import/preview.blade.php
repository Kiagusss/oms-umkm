@extends('admin.layouts.app')

@section('title', 'Pratinjau & Resolusi Duplikat — Import Katalog')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.import.index') }}" class="text-xs text-slate-500 hover:text-slate-800">← Kembali</a>
                <span class="text-xs text-slate-300">/</span>
                <span class="text-xs font-semibold text-emerald-700">Langkah 2: Pratinjau & Resolusi Duplikat</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-800 mt-1">Pratinjau Katalog Masuk</h1>
            <p class="text-sm text-slate-500">Toko Tujuan: <strong class="text-slate-800">{{ $targetStore->name }}</strong> (ID: {{ $targetStore->id }})</p>
        </div>
    </div>

    {{-- Metrics Summary Grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-xs">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Produk</span>
            <p class="text-2xl font-extrabold text-slate-800 mt-1">{{ $summary['total_products'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-xs">
            <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Produk Baru</span>
            <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $summary['new_products'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-xs">
            <span class="text-xs font-bold text-amber-600 uppercase tracking-wider">Duplikat Terdeteksi</span>
            <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ $summary['duplicate_products'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-xs">
            <span class="text-xs font-bold text-blue-600 uppercase tracking-wider">Kategori / Varian</span>
            <p class="text-2xl font-extrabold text-blue-600 mt-1">{{ $summary['total_categories'] }} / {{ $summary['total_variants'] }}</p>
        </div>
    </div>

    {{-- Import Execution Form --}}
    <form method="POST" action="{{ route('admin.import.execute') }}">
        @csrf
        <input type="hidden" name="target_store_id" value="{{ $targetStore->id }}">
        <input type="hidden" name="source_type" value="{{ $sourceType }}">
        <input type="hidden" name="source_identifier" value="{{ $sourceIdentifier }}">
        {{-- Pass raw normalized data as hidden JSON for execution --}}
        <input type="hidden" name="normalized_data" value="{{ json_encode($normalizedData) }}">

        {{-- Strategy Card --}}
        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-xs mb-6">
            <h3 class="text-sm font-bold text-slate-800 mb-2">Strategi Penanganan Duplikat</h3>
            <p class="text-xs text-slate-500 mb-4">
                Pilih tindakan saat sistem mendeteksi produk yang sudah ada sebelumnya (berdasarkan SKU, External ID, atau Kesamaan Nama):
            </p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <label class="flex items-start gap-3 rounded-xl border p-4 cursor-pointer hover:border-slate-300">
                    <input type="radio" name="duplicate_strategy" value="skip" checked class="mt-1 text-emerald-600 focus:ring-emerald-500">
                    <div>
                        <strong class="block text-sm font-bold text-slate-800">Lewati Duplikat (Skip)</strong>
                        <span class="text-xs text-slate-500">Pertahankan data produk lama, hanya import produk yang benar-benar baru.</span>
                    </div>
                </label>

                <label class="flex items-start gap-3 rounded-xl border p-4 cursor-pointer hover:border-slate-300">
                    <input type="radio" name="duplicate_strategy" value="update" class="mt-1 text-emerald-600 focus:ring-emerald-500">
                    <div>
                        <strong class="block text-sm font-bold text-slate-800">Perbarui Data (Update)</strong>
                        <span class="text-xs text-slate-500">Update harga, stok, deskripsi, dan varian pada produk lama yang cocok.</span>
                    </div>
                </label>

                <label class="flex items-start gap-3 rounded-xl border p-4 cursor-pointer hover:border-slate-300">
                    <input type="radio" name="duplicate_strategy" value="create_new" class="mt-1 text-emerald-600 focus:ring-emerald-500">
                    <div>
                        <strong class="block text-sm font-bold text-slate-800">Buat Baru (Salinan)</strong>
                        <span class="text-xs text-slate-500">Tetap import sebagai produk terpisah dengan penyesuaian slug otomatis.</span>
                    </div>
                </label>
            </div>
        </div>

        {{-- Item Preview Table --}}
        <div class="rounded-2xl border border-slate-100 bg-white shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-800">Daftar Produk yang Akan Diimport</h3>
                <span class="text-xs text-slate-400">Total: {{ count($previewProducts) }} produk</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3.5 px-4">Status Duplikat</th>
                            <th class="py-3.5 px-4">Nama Produk</th>
                            <th class="py-3.5 px-4">Kategori</th>
                            <th class="py-3.5 px-4">Harga</th>
                            <th class="py-3.5 px-4">Stok</th>
                            <th class="py-3.5 px-4">SKU / Ext ID</th>
                            <th class="py-3.5 px-4">Varian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($previewProducts as $item)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4">
                                @if($item['is_duplicate'])
                                    <span class="inline-block rounded-md bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-700 border border-amber-200">
                                        ⚠ Duplikat ({{ $item['duplicate_reason'] }})
                                    </span>
                                @else
                                    <span class="inline-block rounded-md bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 border border-emerald-200">
                                        ✓ Produk Baru
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-800">
                                {{ $item['name'] }}
                            </td>
                            <td class="py-3 px-4">
                                {{ $item['category'] ?? '-' }}
                            </td>
                            <td class="py-3 px-4 text-emerald-700 font-bold">
                                Rp {{ number_format($item['price'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4">
                                {{ $item['stock'] }}
                            </td>
                            <td class="py-3 px-4 text-slate-400 font-mono">
                                {{ $item['sku'] ?? $item['external_id'] ?? '-' }}
                            </td>
                            <td class="py-3 px-4">
                                @if(!empty($item['variants']))
                                    <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold text-slate-600">
                                        {{ count($item['variants']) }} varian
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Action Buttons --}}
            <div class="p-5 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-4">
                <a href="{{ route('admin.import.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                    Batal dan Ubah Sumber
                </a>

                <div class="flex items-center gap-3">
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-700 px-6 py-3 text-sm font-bold text-white shadow-sm hover:bg-emerald-800 transition"
                    >
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Eksekusi Import Katalog Sekarang
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
