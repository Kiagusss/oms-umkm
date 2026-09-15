@extends('admin.layouts.app')

@section('title', 'Pembelian PO — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Pembelian & Purchase Orders (PO)',
    'description' => 'Kelola pengadaan bahan baku, restock cabang, dan update biaya modal (HPP).',
    'actionRoute' => route('admin.pembelian.create'),
    'actionLabel' => 'Buat Purchase Order',
])

<div class="overflow-x-auto rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white shadow-xs">
    <table class="w-full border-collapse text-left text-sm">
        <thead>
            <tr class="border-b border-[var(--color-paper-3)] bg-[var(--color-paper-2)] font-semibold text-[var(--color-ink)]">
                <th class="px-6 py-4">No. PO / Invoice</th>
                <th class="px-6 py-4">Cabang</th>
                <th class="px-6 py-4">Supplier</th>
                <th class="px-6 py-4">Tanggal Pembelian</th>
                <th class="px-6 py-4">Total Biaya</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[var(--color-paper-3)] text-[var(--color-ink-2)]">
            @forelse($purchases as $po)
                <tr class="hover:bg-[var(--color-paper-2)] transition-colors">
                    <td class="px-6 py-4 font-mono font-bold text-[var(--color-ink)]">{{ $po->invoice_number }}</td>
                    <td class="px-6 py-4 font-semibold text-slate-700">{{ $po->branch?->name ?? 'Semua Cabang' }}</td>
                    <td class="px-6 py-4 text-slate-800">{{ $po->supplier?->name ?? '—' }}</td>
                    <td class="px-6 py-4 text-xs font-mono text-slate-500">{{ $po->purchase_date->format('d/m/Y') }}</td>
                    <td class="px-6 py-4 font-mono font-bold text-slate-900">Rp {{ number_format($po->total_amount, 0, ',', '.') }}</td>
                    <td class="px-6 py-4">
                        @if($po->status === 'received')
                            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Diterima (Stok Masuk)</span>
                        @elseif($po->status === 'cancelled')
                            <span class="rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700">Dibatalkan</span>
                        @else
                            <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">Menunggu Diterima</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.pembelian.show', $po) }}" class="rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-1.5 text-xs font-medium text-[var(--color-accent)] hover:bg-[var(--color-accent-light)]">Detail & Terima</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-6 py-10 text-center text-[var(--color-ink-3)]">Belum ada transaksi pembelian PO tercatat.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($purchases->hasPages())
    <div class="mt-6">{{ $purchases->links() }}</div>
@endif
@endsection
