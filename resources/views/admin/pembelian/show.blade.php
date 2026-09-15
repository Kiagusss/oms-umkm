@extends('admin.layouts.app')

@section('title', 'Detail PO ' . $purchase->invoice_number . ' — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Purchase Order: ' . $purchase->invoice_number,
    'description' => 'Faktur pengadaan bahan baku dari ' . ($purchase->supplier?->name ?? 'Supplier'),
    'actionRoute' => route('admin.pembelian.index'),
    'actionLabel' => 'Kembali ke Daftar PO',
])

<div class="grid gap-6 lg:grid-cols-3 mb-8">
    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-xs">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Status Faktur</p>
        <div class="mt-2">
            @if($purchase->status === 'received')
                <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-sm font-bold text-emerald-700">
                    <span class="mr-1.5 h-2 w-2 rounded-full bg-emerald-500"></span> Barang Diterima
                </span>
            @elseif($purchase->status === 'cancelled')
                <span class="inline-flex items-center rounded-full bg-rose-50 px-3 py-1 text-sm font-bold text-rose-700">
                    <span class="mr-1.5 h-2 w-2 rounded-full bg-rose-500"></span> Dibatalkan
                </span>
            @else
                <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-sm font-bold text-amber-700">
                    <span class="mr-1.5 h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span> Menunggu Penerimaan
                </span>
            @endif
        </div>
        <p class="mt-2 text-xs text-slate-500">Tanggal: {{ $purchase->purchase_date->format('d F Y') }}</p>
    </div>

    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-xs">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Cabang Penerima</p>
        <p class="mt-2 text-xl font-bold text-slate-900">{{ $purchase->branch?->name ?? 'Semua Cabang' }}</p>
        <p class="mt-1 text-xs text-slate-500">Supplier: {{ $purchase->supplier?->name ?? '—' }}</p>
    </div>

    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-xs">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Nilai Transaksi</p>
        <p class="mt-2 text-3xl font-black text-slate-900 font-mono">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</p>
        <p class="mt-1 text-xs text-slate-500">Dibuat oleh: {{ $purchase->user?->name ?? 'Admin' }}</p>
    </div>
</div>

{{-- Items Table --}}
<div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-xs mb-8">
    <h3 class="text-base font-bold text-slate-800 mb-4">Rincian Item Bahan Baku</h3>
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 font-semibold text-slate-500 text-xs uppercase">
                    <th class="py-3 px-4">Nama Bahan Baku</th>
                    <th class="py-3 px-4">Jumlah Dipesan</th>
                    <th class="py-3 px-4">Harga Beli Satuan</th>
                    <th class="py-3 px-4 text-right">Subtotal Biaya</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @foreach($purchase->items as $item)
                    <tr>
                        <td class="py-3.5 px-4 font-semibold text-slate-800">
                            {{ $item->inventoryItem?->name ?? 'Item #' . $item->inventory_item_id }}
                            <div class="text-xs text-slate-400">SKU: {{ $item->inventoryItem?->sku ?? '—' }}</div>
                        </td>
                        <td class="py-3.5 px-4 font-mono font-bold">{{ number_format($item->quantity, 2, ',', '.') }} {{ $item->inventoryItem?->unit }}</td>
                        <td class="py-3.5 px-4 font-mono text-xs">Rp {{ number_format($item->unit_cost, 0, ',', '.') }}</td>
                        <td class="py-3.5 px-4 font-mono font-bold text-right text-slate-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-slate-200 font-bold text-slate-800">
                    <td colspan="3" class="py-3.5 px-4 text-right">Total Transaksi:</td>
                    <td class="py-3.5 px-4 text-right font-mono text-base text-slate-900">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    @if($purchase->notes)
        <div class="mt-4 rounded-xl bg-slate-50 p-4 text-xs text-slate-600">
            <span class="font-bold">Catatan PO:</span> {{ $purchase->notes }}
        </div>
    @endif
</div>

{{-- Action Footer --}}
@if($purchase->status === 'draft' || $purchase->status === 'ordered')
    <div class="rounded-2xl border border-amber-200 bg-amber-50/60 p-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h4 class="text-sm font-bold text-amber-950">Konfirmasi Penerimaan Fisik Barang</h4>
            <p class="text-xs text-amber-800 mt-0.5">
                Mengklik "Terima Barang" akan secara otomatis menambah stok cabang {{ $purchase->branch?->name }}, mencatat buku kas/stok mutasi, serta memperbarui moving average HPP bahan baku.
            </p>
        </div>
        <div class="flex gap-3">
            <form method="POST" action="{{ route('admin.pembelian.cancel', $purchase) }}" onsubmit="return confirm('Batalkan purchase order ini?')">
                @csrf
                <button type="submit" class="rounded-xl border border-rose-300 bg-white px-4 py-2 text-xs font-bold text-rose-700 hover:bg-rose-50">Batalkan PO</button>
            </form>
            <form method="POST" action="{{ route('admin.pembelian.receive', $purchase) }}" onsubmit="return confirm('Konfirmasi bahwa barang sudah diterima secara fisik di gudang cabang?')">
                @csrf
                <button type="submit" class="rounded-xl bg-emerald-700 px-5 py-2 text-xs font-bold text-white shadow-sm hover:bg-emerald-800">Terima Barang & Tambahkan ke Stok</button>
            </form>
        </div>
    </div>
@endif
@endsection
