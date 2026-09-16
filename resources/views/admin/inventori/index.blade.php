@extends('admin.layouts.app')

@section('title', 'Inventori & Stok Bahan — Admin Pempek')

@section('content')
<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[var(--color-ink)]">Inventori & Bahan Baku</h1>
        <p class="mt-1 text-sm text-[var(--color-ink-3)]">Kelola stok bahan baku, packaging, dan monitor peringatan stok rendah.</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.inventori.mutasi') }}" class="inline-flex items-center gap-2 rounded-[var(--radius-xl)] border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            Buku Mutasi Stok
        </a>
        <a href="{{ route('admin.inventori.create') }}" class="inline-flex items-center gap-2 rounded-[var(--radius-xl)] bg-[var(--color-accent)] px-5 py-2.5 text-sm font-semibold text-white transition-all hover:bg-[var(--color-accent-hover)]">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14" /></svg>
            Tambah Bahan
        </a>
    </div>
</div>

{{-- Filter Card --}}
<div class="mb-6 rounded-[var(--radius-xl)] border border-slate-100 bg-white p-4 shadow-xs">
    <form method="GET" action="{{ route('admin.inventori.index') }}" class="flex flex-wrap items-center gap-3">
        <div class="flex-1 min-w-[200px]">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau SKU..." class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-sm outline-none focus:border-emerald-600">
        </div>
        <div>
            <select name="branch_id" class="rounded-xl border border-slate-200 px-3.5 py-2 text-sm outline-none focus:border-emerald-600">
                <option value="">Semua Cabang (Akumulasi)</option>
                @foreach($branches as $b)
                    <option value="{{ $b->id }}" @selected($branchId == $b->id)>{{ $b->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-2">
            <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-600">
                <input type="checkbox" name="low_stock" value="1" @checked(request('low_stock')) class="rounded border-slate-300 text-emerald-600">
                Stok Menipis Saja
            </label>
        </div>
        <button type="submit" class="rounded-xl bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Filter</button>
        @if(request()->hasAny(['search', 'branch_id', 'low_stock']))
            <a href="{{ route('admin.inventori.index') }}" class="text-xs text-slate-500 hover:text-slate-800">Reset</a>
        @endif
    </form>
</div>

{{-- Inventory Table --}}
<div class="overflow-x-auto rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white shadow-xs">
    <table class="w-full border-collapse text-left text-sm">
        <thead>
            <tr class="border-b border-[var(--color-paper-3)] bg-[var(--color-paper-2)] font-semibold text-[var(--color-ink)]">
                <th class="px-6 py-4">SKU</th>
                <th class="px-6 py-4">Nama Bahan</th>
                <th class="px-6 py-4">Kategori</th>
                <th class="px-6 py-4">Satuan</th>
                <th class="px-6 py-4">Biaya Satuan (HPP)</th>
                <th class="px-6 py-4">Stok Saat Ini</th>
                <th class="px-6 py-4">Batas Min.</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[var(--color-paper-3)] text-[var(--color-ink-2)]" x-data="{ adjustModal: false, selectedItem: null }">
            @forelse($items as $item)
                @php
                    $stock = (float) ($item->current_stock ?? 0);
                    $minStock = (float) ($item->min_stock ?? 0);
                    $isLow = $stock <= $minStock;
                    $isOut = $stock <= 0;
                @endphp
                <tr class="hover:bg-[var(--color-paper-2)] transition-colors">
                    <td class="px-6 py-4 font-mono text-xs font-semibold text-slate-600">{{ $item->sku }}</td>
                    <td class="px-6 py-4 font-semibold text-[var(--color-ink)]">{{ $item->name }}</td>
                    <td class="px-6 py-4">
                        <span class="rounded-lg bg-slate-100 px-2 py-0.5 text-xs text-slate-700 capitalize">
                            {{ str_replace('_', ' ', (string) ($item->category ?? 'Umum')) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-xs font-medium">{{ $item->unit }}</td>
                    <td class="px-6 py-4 tabular-nums font-medium text-slate-800">Rp {{ number_format((float) ($item->unit_cost ?? 0), 0, ',', '.') }}</td>
                    <td class="px-6 py-4 tabular-nums font-bold {{ $isOut ? 'text-rose-600' : ($isLow ? 'text-amber-600' : 'text-slate-800') }}">
                        {{ number_format($stock, 2, ',', '.') }}
                    </td>
                    <td class="px-6 py-4 tabular-nums text-xs text-slate-500">{{ number_format($minStock, 2, ',', '.') }}</td>
                    <td class="px-6 py-4">
                        @if($isOut)
                            <span class="rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700">Habis</span>
                        @elseif($isLow)
                            <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">Menipis</span>
                        @else
                            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Aman</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end items-center gap-2">
                            {{-- Adjust Stock Button --}}
                            <button type="button" @click="selectedItem = {{ json_encode($item) }}; adjustModal = true" class="rounded-[var(--radius-md)] border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                Opname
                            </button>
                            <a href="{{ route('admin.inventori.edit', $item) }}" class="rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-1.5 text-xs font-medium text-[var(--color-accent)] hover:bg-[var(--color-accent-light)]">Edit</a>
                            <form method="POST" action="{{ route('admin.inventori.destroy', $item) }}" onsubmit="return confirm('Hapus item inventori ini?')">
                                @csrf @method('DELETE')
                                <button class="rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-1.5 text-xs font-medium text-[var(--color-danger)] hover:bg-red-50">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="px-6 py-10 text-center text-[var(--color-ink-3)]">Belum ada data inventori.</td></tr>
            @endforelse

            {{-- Modal Penyesuaian Stok (Opname) --}}
            <div x-show="adjustModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                <div @click.away="adjustModal = false" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                    <h3 class="text-lg font-bold text-slate-800">Penyesuaian Stok (Opname)</h3>
                    <p class="mt-1 text-xs text-slate-500" x-text="selectedItem ? selectedItem.name + ' (' + selectedItem.sku + ')' : ''"></p>
                    
                    <form :action="'{{ url('admin/inventori') }}/' + (selectedItem ? selectedItem.id : '') + '/adjust'" method="POST" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Cabang</label>
                            <select name="branch_id" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" @selected($branchId == $b->id)>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Jumlah</label>
                                <input type="number" step="0.001" name="quantity" required placeholder="0.00" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Tipe Mutasi</label>
                                <select name="type" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <option value="in">Masuk (+)</option>
                                    <option value="out">Keluar / Rusak (-)</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan Penyesuaian</label>
                            <input type="text" name="notes" required placeholder="Misal: Selisih stock opname mingguan" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        </div>
                        <div class="mt-6 flex justify-end gap-2">
                            <button type="button" @click="adjustModal = false" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                            <button type="submit" class="rounded-xl bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Simpan Mutasi</button>
                        </div>
                    </form>
                </div>
            </div>
        </tbody>
    </table>
</div>

@if($items->hasPages())
    <div class="mt-6">{{ $items->links() }}</div>
@endif
@endsection
