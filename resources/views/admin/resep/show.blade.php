@extends('admin.layouts.app')

@section('title', 'Detail Resep — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Detail Resep: ' . ($recipe->product?->name ?? 'Produk') . ($recipe->variant ? ' (' . $recipe->variant->name . ')' : ''),
    'description' => 'Rincian Bill of Materials (BOM) dan jejak riwayat kalkulasi HPP.',
    'actionRoute' => route('admin.resep.index'),
    'actionLabel' => 'Kembali',
])

@php
    $sellingPrice = $recipe->variant ? $recipe->variant->price : ($recipe->product?->price ?? 0);
    $margin = $sellingPrice - $recipe->calculated_cost;
    $marginPct = $sellingPrice > 0 ? round(($margin / $sellingPrice) * 100, 1) : 0;
    $totalBatchCost = $recipe->calculated_cost * $recipe->yield;
@endphp

<div class="grid gap-6 lg:grid-cols-3 mb-8">
    {{-- Summary Cards --}}
    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-xs">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">HPP per Porsi</p>
        <p class="mt-2 text-3xl font-black text-emerald-700 font-mono">Rp {{ number_format($recipe->calculated_cost, 0, ',', '.') }}</p>
        <p class="mt-1 text-xs text-slate-500">Hasil batch: {{ $recipe->yield }} porsi</p>
    </div>

    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-xs">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Harga Jual Saat Ini</p>
        <p class="mt-2 text-3xl font-black text-slate-800 font-mono">Rp {{ number_format($sellingPrice, 0, ',', '.') }}</p>
        <p class="mt-1 text-xs text-slate-500">Katalog POS / Web Publik</p>
    </div>

    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-xs">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Margin Laba Kotor</p>
        <p class="mt-2 text-3xl font-black {{ $marginPct >= 40 ? 'text-emerald-600' : 'text-amber-600' }} font-mono">{{ $marginPct }}%</p>
        <p class="mt-1 text-xs text-slate-500">Laba kotor: Rp {{ number_format($margin, 0, ',', '.') }} / porsi</p>
    </div>
</div>

{{-- Ingredients Table --}}
<div class="mb-8 rounded-2xl border border-slate-100 bg-white p-6 shadow-xs">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-slate-800">Komposisi Bahan (Bill of Materials)</h2>
        <a href="{{ route('admin.resep.edit', $recipe) }}" class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Edit Komposisi</a>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 font-semibold text-slate-500 text-xs uppercase">
                    <th class="py-3 px-4">Nama Bahan</th>
                    <th class="py-3 px-4">Kategori</th>
                    <th class="py-3 px-4">Kebutuhan per Batch</th>
                    <th class="py-3 px-4">Kebutuhan per Porsi</th>
                    <th class="py-3 px-4">Biaya Satuan</th>
                    <th class="py-3 px-4 text-right">Subtotal Biaya</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700 text-sm">
                @foreach($recipe->items as $item)
                    @php
                        $unitCost = (float) ($item->inventoryItem?->cost_per_unit ?? $item->inventoryItem?->unit_cost ?? $item->cost_per_unit ?? 0);
                        $cost = $item->quantity * $unitCost;
                        $perPortionQty = $recipe->yield > 0 ? $item->quantity / $recipe->yield : 0;
                    @endphp
                    <tr>
                        <td class="py-3.5 px-4 font-semibold text-slate-800">{{ $item->inventoryItem?->name ?? 'Bahan #' . $item->inventory_item_id }}</td>
                        <td class="py-3.5 px-4 text-xs capitalize text-slate-500">{{ str_replace('_', ' ', $item->inventoryItem?->category ?? '') }}</td>
                        <td class="py-3.5 px-4 tabular-nums">{{ number_format($item->quantity, 3, ',', '.') }} {{ $item->inventoryItem?->unit ?? $item->unit }}</td>
                        <td class="py-3.5 px-4 tabular-nums text-xs text-slate-500">{{ number_format($perPortionQty, 3, ',', '.') }} {{ $item->inventoryItem?->unit ?? $item->unit }}</td>
                        <td class="py-3.5 px-4 tabular-nums text-xs">Rp {{ number_format($unitCost, 0, ',', '.') }} / {{ $item->inventoryItem?->unit ?? $item->unit }}</td>
                        <td class="py-3.5 px-4 tabular-nums font-semibold text-right text-slate-900">Rp {{ number_format($cost, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-slate-200 font-bold text-slate-800">
                    <td colspan="5" class="py-3.5 px-4 text-right">Total Biaya Komposisi Batch:</td>
                    <td class="py-3.5 px-4 text-right text-emerald-800 font-mono">Rp {{ number_format($totalBatchCost, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    @if($recipe->notes ?? $recipe->instructions)
        <div class="mt-6 rounded-xl bg-slate-50 p-4">
            <h4 class="text-xs font-bold text-slate-600 uppercase">Petunjuk SOP Pembuatan:</h4>
            <p class="mt-1 text-sm text-slate-700 whitespace-pre-line">{{ $recipe->notes ?? $recipe->instructions }}</p>
        </div>
    @endif
</div>

{{-- HPP Revision Audit Trail --}}
<div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-xs">
    <h2 class="text-lg font-bold text-slate-800 mb-4">Riwayat Perubahan HPP (Audit Trail)</h2>
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left text-xs">
            <thead>
                <tr class="border-b border-slate-100 font-semibold text-slate-500 uppercase">
                    <th class="py-3 px-4">Waktu</th>
                    <th class="py-3 px-4">HPP Lama</th>
                    <th class="py-3 px-4">HPP Baru</th>
                    <th class="py-3 px-4">Selisih</th>
                    <th class="py-3 px-4">Alasan Perubahan</th>
                    <th class="py-3 px-4">Oleh</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @forelse($history as $h)
                    @php
                        $diff = $h->new_cost - $h->old_cost;
                    @endphp
                    <tr>
                        <td class="py-3 px-4 font-mono text-slate-500">{{ $h->created_at->format('d/m/Y H:i') }}</td>
                        <td class="py-3 px-4 tabular-nums">Rp {{ number_format($h->old_cost, 0, ',', '.') }}</td>
                        <td class="py-3 px-4 tabular-nums font-bold text-slate-800">Rp {{ number_format($h->new_cost, 0, ',', '.') }}</td>
                        <td class="py-3 px-4 tabular-nums font-semibold {{ $diff > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                            {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-slate-600">{{ $h->reason ?? 'Kalkulasi sistem' }}</td>
                        <td class="py-3 px-4 text-slate-600">{{ $h->user?->name ?? 'Sistem' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-6 text-center text-slate-400">Belum ada riwayat perubahan HPP.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
