@extends('admin.layouts.app')

@section('title', 'Detail Transfer ' . $transfer->transfer_number . ' — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Transfer Stok: ' . $transfer->transfer_number,
    'description' => 'Mutasi bahan baku dari ' . ($transfer->fromBranch?->name ?? 'Asal') . ' ke ' . ($transfer->toBranch?->name ?? 'Tujuan'),
    'actionRoute' => route('admin.transfer.index'),
    'actionLabel' => 'Kembali ke Daftar Transfer',
])

<div class="grid gap-6 lg:grid-cols-3 mb-8">
    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-xs">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Status Pengiriman</p>
        <div class="mt-2">
            @if($transfer->status === 'completed')
                <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-sm font-bold text-emerald-700">
                    <span class="mr-1.5 h-2 w-2 rounded-full bg-emerald-500"></span> Selesai Diterima
                </span>
            @elseif($transfer->status === 'in_transit')
                <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-sm font-bold text-blue-700">
                    <span class="mr-1.5 h-2 w-2 rounded-full bg-blue-500 animate-pulse"></span> Sedang Dalam Perjalanan
                </span>
            @elseif($transfer->status === 'cancelled')
                <span class="inline-flex items-center rounded-full bg-rose-50 px-3 py-1 text-sm font-bold text-rose-700">
                    <span class="mr-1.5 h-2 w-2 rounded-full bg-rose-500"></span> Dibatalkan
                </span>
            @else
                <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-sm font-bold text-amber-700">
                    <span class="mr-1.5 h-2 w-2 rounded-full bg-amber-500"></span> Draft / Menunggu Kirim
                </span>
            @endif
        </div>
        <p class="mt-2 text-xs text-slate-500">Dibuat: {{ $transfer->created_at->format('d/m/Y H:i') }}</p>
    </div>

    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-xs">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Rute Distribusi</p>
        <div class="mt-2 flex items-center gap-3">
            <span class="font-bold text-slate-800 text-sm">{{ $transfer->fromBranch?->name }}</span>
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            <span class="font-bold text-emerald-700 text-sm">{{ $transfer->toBranch?->name }}</span>
        </div>
        <p class="mt-2 text-xs text-slate-500">Oleh: {{ $transfer->creator?->name ?? 'Admin' }}</p>
    </div>

    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-xs">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Jejak Waktu</p>
        <div class="mt-2 space-y-1 text-xs text-slate-700">
            <p><span class="font-semibold text-slate-500">Dikirim:</span> {{ $transfer->shipped_at ? $transfer->shipped_at->format('d/m/Y H:i') : '—' }}</p>
            <p><span class="font-semibold text-slate-500">Diterima:</span> {{ $transfer->received_at ? $transfer->received_at->format('d/m/Y H:i') : '—' }}</p>
        </div>
    </div>
</div>

{{-- Transferred Items --}}
<div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-xs mb-8">
    <h3 class="text-base font-bold text-slate-800 mb-4">Daftar Bahan Yang Ditransfer</h3>
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 font-semibold text-slate-500 text-xs uppercase">
                    <th class="py-3 px-4">Nama Bahan</th>
                    <th class="py-3 px-4">Kategori</th>
                    <th class="py-3 px-4 text-right">Jumlah Ditransfer</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @foreach($transfer->items as $item)
                    <tr>
                        <td class="py-3.5 px-4 font-semibold text-slate-800">
                            {{ $item->inventoryItem?->name ?? 'Item #' . $item->inventory_item_id }}
                            <div class="text-xs text-slate-400">SKU: {{ $item->inventoryItem?->sku ?? '—' }}</div>
                        </td>
                        <td class="py-3.5 px-4 text-xs capitalize text-slate-500">{{ str_replace('_', ' ', $item->inventoryItem?->category ?? '') }}</td>
                        <td class="py-3.5 px-4 font-mono font-bold text-right text-base text-slate-900">
                            {{ number_format($item->quantity, 2, ',', '.') }} {{ $item->inventoryItem?->unit }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($transfer->notes)
        <div class="mt-4 rounded-xl bg-slate-50 p-4 text-xs text-slate-600">
            <span class="font-bold">Catatan Transfer:</span> {{ $transfer->notes }}
        </div>
    @endif
</div>

{{-- Lifecycle Actions --}}
@if($transfer->status === 'draft')
    <div class="rounded-2xl border border-blue-200 bg-blue-50/60 p-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h4 class="text-sm font-bold text-blue-950">Langkah 1: Pengiriman Bahan (Dispatched)</h4>
            <p class="text-xs text-blue-800 mt-0.5">
                Mengklik "Kirim Barang" akan memotong stok di cabang asal ({{ $transfer->fromBranch?->name }}) dan mencatat mutasi keluar.
            </p>
        </div>
        <div class="flex gap-3">
            <form method="POST" action="{{ route('admin.transfer.cancel', $transfer) }}" onsubmit="return confirm('Batalkan transfer ini?')">
                @csrf
                <button type="submit" class="rounded-xl border border-rose-300 bg-white px-4 py-2 text-xs font-bold text-rose-700 hover:bg-rose-50">Batalkan</button>
            </form>
            <form method="POST" action="{{ route('admin.transfer.ship', $transfer) }}" onsubmit="return confirm('Konfirmasi bahwa barang telah diberangkatkan dari cabang asal?')">
                @csrf
                <button type="submit" class="rounded-xl bg-blue-600 px-5 py-2 text-xs font-bold text-white shadow-sm hover:bg-blue-700">Kirim Barang Sekarang</button>
            </form>
        </div>
    </div>
@elseif($transfer->status === 'in_transit')
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50/60 p-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h4 class="text-sm font-bold text-emerald-950">Langkah 2: Konfirmasi Kedatangan di Cabang Tujuan</h4>
            <p class="text-xs text-emerald-800 mt-0.5">
                Mengklik "Terima Barang" akan menambah stok di cabang tujuan ({{ $transfer->toBranch?->name }}) dan mencatat mutasi masuk.
            </p>
        </div>
        <div>
            <form method="POST" action="{{ route('admin.transfer.receive', $transfer) }}" onsubmit="return confirm('Konfirmasi bahwa paket barang telah sampai dan dicek di gudang tujuan?')">
                @csrf
                <button type="submit" class="rounded-xl bg-emerald-700 px-6 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-emerald-800">Terima Barang di Cabang Tujuan</button>
            </form>
        </div>
    </div>
@endif
@endsection
