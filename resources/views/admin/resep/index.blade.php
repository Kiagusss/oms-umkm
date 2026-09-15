@extends('admin.layouts.app')

@section('title', 'Resep & BOM (HPP) — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Resep & Bill of Materials (BOM)',
    'description' => 'Kelola komposisi bahan dan kalkulasi Harga Pokok Penjualan (HPP) otomatis per porsi.',
    'actionRoute' => route('admin.resep.create'),
    'actionLabel' => 'Buat Resep Baru',
])

<div class="overflow-x-auto rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white shadow-xs">
    <table class="w-full border-collapse text-left text-sm">
        <thead>
            <tr class="border-b border-[var(--color-paper-3)] bg-[var(--color-paper-2)] font-semibold text-[var(--color-ink)]">
                <th class="px-6 py-4">Produk / Menu</th>
                <th class="px-6 py-4">Varian</th>
                <th class="px-6 py-4">Hasil (Porsi)</th>
                <th class="px-6 py-4">Total Biaya Batch</th>
                <th class="px-6 py-4">HPP per Porsi</th>
                <th class="px-6 py-4">Harga Jual</th>
                <th class="px-6 py-4">Margin Kotor</th>
                <th class="px-6 py-4 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[var(--color-paper-3)] text-[var(--color-ink-2)]">
            @forelse($recipes as $recipe)
                @php
                    $sellingPrice = $recipe->variant ? $recipe->variant->price : ($recipe->product?->price ?? 0);
                    $margin = $sellingPrice - $recipe->calculated_cost;
                    $marginPct = $sellingPrice > 0 ? round(($margin / $sellingPrice) * 100, 1) : 0;
                @endphp
                <tr class="hover:bg-[var(--color-paper-2)] transition-colors">
                    <td class="px-6 py-4 font-semibold text-[var(--color-ink)]">
                        <a href="{{ route('admin.resep.show', $recipe) }}" class="hover:text-emerald-700">
                            {{ $recipe->product?->name ?? 'Produk #' . $recipe->product_id }}
                        </a>
                    </td>
                    <td class="px-6 py-4 text-xs font-medium text-slate-600">
                        {{ $recipe->variant?->name ?? 'Standar (Base)' }}
                    </td>
                    <td class="px-6 py-4 tabular-nums">{{ number_format($recipe->yield, 0, ',', '.') }} porsi</td>
                    <td class="px-6 py-4 tabular-nums text-slate-600">Rp {{ number_format($recipe->calculated_cost * $recipe->yield, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 tabular-nums font-bold text-emerald-700">Rp {{ number_format($recipe->calculated_cost, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 tabular-nums font-semibold text-slate-800">Rp {{ number_format($sellingPrice, 0, ',', '.') }}</td>
                    <td class="px-6 py-4">
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $marginPct >= 40 ? 'bg-emerald-50 text-emerald-700' : ($marginPct > 0 ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700') }}">
                            {{ $marginPct }}% (Rp {{ number_format($margin, 0, ',', '.') }})
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end items-center gap-2">
                            <form method="POST" action="{{ route('admin.resep.recalculate', $recipe) }}">
                                @csrf
                                <button type="submit" title="Hitung ulang HPP berdasarkan harga bahan terkini" class="rounded-[var(--radius-md)] border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                    Hitung Ulang
                                </button>
                            </form>
                            <a href="{{ route('admin.resep.show', $recipe) }}" class="rounded-[var(--radius-md)] border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">Detail</a>
                            <a href="{{ route('admin.resep.edit', $recipe) }}" class="rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-1.5 text-xs font-medium text-[var(--color-accent)] hover:bg-[var(--color-accent-light)]">Edit</a>
                            <form method="POST" action="{{ route('admin.resep.destroy', $recipe) }}" onsubmit="return confirm('Hapus resep ini?')">
                                @csrf @method('DELETE')
                                <button class="rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-1.5 text-xs font-medium text-[var(--color-danger)] hover:bg-red-50">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="px-6 py-10 text-center text-[var(--color-ink-3)]">Belum ada resep terdaftar. Klik "Buat Resep Baru" untuk memulai kalkulasi HPP.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($recipes->hasPages())
    <div class="mt-6">{{ $recipes->links() }}</div>
@endif
@endsection
