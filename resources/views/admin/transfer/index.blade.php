@extends('admin.layouts.app')

@section('title', 'Transfer Antar Cabang — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Transfer Stok Antar Cabang',
    'description' => 'Kelola mutasi dan distribusi bahan baku antar gudang cabang & pusat.',
    'actionRoute' => route('admin.transfer.create'),
    'actionLabel' => 'Buat Permintaan Transfer',
])

<div class="overflow-x-auto rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white shadow-xs">
    <table class="w-full border-collapse text-left text-sm">
        <thead>
            <tr class="border-b border-[var(--color-paper-3)] bg-[var(--color-paper-2)] font-semibold text-[var(--color-ink)]">
                <th class="px-6 py-4">No. Transfer</th>
                <th class="px-6 py-4">Cabang Asal</th>
                <th class="px-6 py-4">Cabang Tujuan</th>
                <th class="px-6 py-4">Total Item</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4">Tanggal Pengiriman</th>
                <th class="px-6 py-4 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[var(--color-paper-3)] text-[var(--color-ink-2)]">
            @forelse($transfers as $tr)
                <tr class="hover:bg-[var(--color-paper-2)] transition-colors">
                    <td class="px-6 py-4 font-mono font-bold text-[var(--color-ink)]">{{ $tr->transfer_number }}</td>
                    <td class="px-6 py-4 font-semibold text-slate-800">{{ $tr->fromBranch?->name }}</td>
                    <td class="px-6 py-4 font-semibold text-slate-800">
                        <span class="inline-flex items-center gap-1">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            {{ $tr->toBranch?->name }}
                        </span>
                    </td>
                    <td class="px-6 py-4 font-mono text-xs">{{ $tr->items->count() }} jenis bahan</td>
                    <td class="px-6 py-4">
                        @if($tr->status === 'completed')
                            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Selesai Diterima</span>
                        @elseif($tr->status === 'in_transit')
                            <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">Dalam Perjalanan</span>
                        @elseif($tr->status === 'cancelled')
                            <span class="rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700">Dibatalkan</span>
                        @else
                            <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">Draft / Siap Kirim</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-xs font-mono text-slate-500">{{ $tr->shipped_at ? $tr->shipped_at->format('d/m/Y H:i') : '—' }}</td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.transfer.show', $tr) }}" class="rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-1.5 text-xs font-medium text-[var(--color-accent)] hover:bg-[var(--color-accent-light)]">Detail & Proses</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-6 py-10 text-center text-[var(--color-ink-3)]">Belum ada transfer stok antar cabang.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($transfers->hasPages())
    <div class="mt-6">{{ $transfers->links() }}</div>
@endif
@endsection
