@extends('admin.layouts.app')

@section('title', 'Detail Import #' . $import->id . ' — Multi-Tenant OMS')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.import.history') }}" class="text-xs text-slate-500 hover:text-slate-800">← Riwayat Import</a>
                <span class="text-xs text-slate-300">/</span>
                <span class="text-xs font-semibold text-emerald-700">Detail Job #{{ $import->id }}</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-800 mt-1">Audit Trail Import Produk</h1>
            <p class="text-sm text-slate-500">Toko: <strong class="text-slate-800">{{ $import->store->name ?? '-' }}</strong> | Sumber: {{ $import->source_type }}</p>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
        <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-xs">
            <span class="text-[11px] font-bold text-slate-400 uppercase">Total Baris</span>
            <p class="text-xl font-black text-slate-800 mt-1">{{ $import->total_items }}</p>
        </div>
        <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-xs">
            <span class="text-[11px] font-bold text-emerald-600 uppercase">Berhasil Diimport</span>
            <p class="text-xl font-black text-emerald-600 mt-1">{{ $import->imported_items }}</p>
        </div>
        <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-xs">
            <span class="text-[11px] font-bold text-amber-600 uppercase">Dilewati (Skip)</span>
            <p class="text-xl font-black text-amber-600 mt-1">{{ $import->skipped_items }}</p>
        </div>
        <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-xs">
            <span class="text-[11px] font-bold text-rose-600 uppercase">Gagal</span>
            <p class="text-xl font-black text-rose-600 mt-1">{{ $import->failed_items }}</p>
        </div>
        <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-xs">
            <span class="text-[11px] font-bold text-slate-400 uppercase">Status</span>
            <p class="text-base font-bold text-slate-800 mt-1 capitalize">{{ $import->status }}</p>
        </div>
    </div>

    {{-- Items Table --}}
    <div class="rounded-2xl border border-slate-100 bg-white shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Detail Log Per Item ({{ $items->total() }})</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">Tindakan</th>
                        <th class="py-3 px-4">Nama Produk</th>
                        <th class="py-3 px-4">Ext ID / SKU</th>
                        <th class="py-3 px-4">Keterangan / Pesan</th>
                        <th class="py-3 px-4 text-right">ID Produk Terkait</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @foreach($items as $it)
                    <tr class="hover:bg-slate-50/50">
                        <td class="py-3 px-4">
                            @if($it->action_taken === 'created')
                                <span class="rounded-md bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 border border-emerald-200">Dibuat</span>
                            @elseif($it->action_taken === 'updated')
                                <span class="rounded-md bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-700 border border-blue-200">Diperbarui</span>
                            @elseif($it->action_taken === 'skipped')
                                <span class="rounded-md bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-700 border border-amber-200">Dilewati</span>
                            @else
                                <span class="rounded-md bg-rose-50 px-2 py-0.5 text-[10px] font-bold text-rose-700 border border-rose-200">Gagal</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-bold text-slate-800">
                            {{ $it->raw_data['name'] ?? $it->error_message ?? '-' }}
                        </td>
                        <td class="py-3 px-4 font-mono text-slate-400">
                            {{ $it->external_id ?? ($it->raw_data['sku'] ?? '-') }}
                        </td>
                        <td class="py-3 px-4 text-slate-500">
                            {{ $it->error_message ?? 'Berhasil diproses sesuai konfigurasi.' }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono">
                            @if($it->product_id)
                                <span class="rounded bg-slate-100 px-2 py-0.5 text-[11px] font-bold text-slate-700">#{{ $it->product_id }}</span>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $items->links() }}
        </div>
    </div>
</div>
@endsection
