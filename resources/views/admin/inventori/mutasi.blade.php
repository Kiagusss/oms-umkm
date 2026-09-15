@extends('admin.layouts.app')

@section('title', 'Buku Mutasi Stok — Admin Pempek')

@section('content')
<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[var(--color-ink)]">Buku Mutasi Stok (Ledger)</h1>
        <p class="mt-1 text-sm text-[var(--color-ink-3)]">Riwayat lengkap mutasi masuk, keluar, penjualan, transfer, dan opname stok.</p>
    </div>
    <a href="{{ route('admin.inventori.index') }}" class="inline-flex items-center gap-2 rounded-[var(--radius-xl)] border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
        Kembali ke Inventori
    </a>
</div>

{{-- Filter Card --}}
<div class="mb-6 rounded-[var(--radius-xl)] border border-slate-100 bg-white p-4 shadow-xs">
    <form method="GET" action="{{ route('admin.inventori.mutasi') }}" class="flex flex-wrap items-center gap-3">
        <div>
            <select name="branch_id" class="rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:border-emerald-600">
                <option value="">Semua Cabang</option>
                @foreach($branches as $b)
                    <option value="{{ $b->id }}" @selected(request('branch_id') == $b->id)>{{ $b->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select name="type" class="rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:border-emerald-600">
                <option value="">Semua Tipe Mutasi</option>
                <option value="purchase" @selected(request('type') == 'purchase')>Pembelian (Masuk)</option>
                <option value="sale" @selected(request('type') == 'sale')>Penjualan POS/Web (Keluar)</option>
                <option value="sale_return" @selected(request('type') == 'sale_return')>Retur / Batal Jual</option>
                <option value="transfer_in" @selected(request('type') == 'transfer_in')>Transfer Masuk</option>
                <option value="transfer_out" @selected(request('type') == 'transfer_out')>Transfer Keluar</option>
                <option value="adjustment" @selected(request('type') == 'adjustment')>Penyesuaian Opname</option>
            </select>
        </div>
        <div>
            <input type="date" name="start_date" value="{{ request('start_date') }}" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
        </div>
        <span class="text-xs text-slate-400">s/d</span>
        <div>
            <input type="date" name="end_date" value="{{ request('end_date') }}" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
        </div>
        <button type="submit" class="rounded-xl bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Filter</button>
        @if(request()->hasAny(['branch_id', 'type', 'start_date', 'end_date']))
            <a href="{{ route('admin.inventori.mutasi') }}" class="text-xs text-slate-500 hover:text-slate-800">Reset</a>
        @endif
    </form>
</div>

{{-- Movements Table --}}
<div class="overflow-x-auto rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white shadow-xs">
    <table class="w-full border-collapse text-left text-sm">
        <thead>
            <tr class="border-b border-[var(--color-paper-3)] bg-[var(--color-paper-2)] font-semibold text-[var(--color-ink)]">
                <th class="px-6 py-4">Waktu</th>
                <th class="px-6 py-4">Cabang</th>
                <th class="px-6 py-4">Item Bahan</th>
                <th class="px-6 py-4">Tipe</th>
                <th class="px-6 py-4">Perubahan</th>
                <th class="px-6 py-4">Saldo Akhir</th>
                <th class="px-6 py-4">Referensi</th>
                <th class="px-6 py-4">Catatan</th>
                <th class="px-6 py-4">Operator</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[var(--color-paper-3)] text-[var(--color-ink-2)]">
            @forelse($movements as $m)
                @php
                    $isPositive = $m->quantity > 0;
                    $typeBadge = match($m->type) {
                        'purchase'    => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'label' => 'Pembelian'],
                        'sale'        => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'label' => 'Penjualan'],
                        'sale_return' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'label' => 'Batal / Retur'],
                        'transfer_in' => ['bg' => 'bg-sky-50', 'text' => 'text-sky-700', 'label' => 'Transfer Masuk'],
                        'transfer_out'=> ['bg' => 'bg-orange-50', 'text' => 'text-orange-700', 'label' => 'Transfer Keluar'],
                        default       => ['bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'label' => 'Opname/Adjust'],
                    };
                @endphp
                <tr class="hover:bg-[var(--color-paper-2)] transition-colors text-xs">
                    <td class="px-6 py-4 whitespace-nowrap text-slate-500 font-mono">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-6 py-4 font-semibold text-slate-800">{{ $m->branch?->name ?? 'Pusat' }}</td>
                    <td class="px-6 py-4 font-medium text-slate-900">{{ $m->inventoryItem?->name ?? 'Item #' . $m->inventory_item_id }}</td>
                    <td class="px-6 py-4">
                        <span class="rounded-full px-2.5 py-0.5 font-semibold {{ $typeBadge['bg'] }} {{ $typeBadge['text'] }}">
                            {{ $typeBadge['label'] }}
                        </span>
                    </td>
                    <td class="px-6 py-4 tabular-nums font-bold {{ $isPositive ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ $isPositive ? '+' : '' }}{{ number_format($m->quantity, 2, ',', '.') }} {{ $m->inventoryItem?->unit }}
                    </td>
                    <td class="px-6 py-4 tabular-nums font-semibold text-slate-700">
                        {{ number_format($m->balance_after, 2, ',', '.') }}
                    </td>
                    <td class="px-6 py-4 font-mono text-slate-600">
                        @if($m->reference_type && $m->reference_id)
                            {{ class_basename($m->reference_type) }} #{{ $m->reference_id }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-6 py-4 text-slate-500 max-w-xs truncate">{{ $m->notes ?? '—' }}</td>
                    <td class="px-6 py-4 text-slate-600">{{ $m->user?->name ?? 'Sistem' }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="px-6 py-10 text-center text-[var(--color-ink-3)]">Belum ada catatan mutasi stok.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($movements->hasPages())
    <div class="mt-6">{{ $movements->links() }}</div>
@endif
@endsection
