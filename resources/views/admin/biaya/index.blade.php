@extends('admin.layouts.app')

@section('title', 'Biaya Operasional — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Biaya & Pengeluaran Operasional',
    'description' => 'Pencatatan beban usaha: gaji, sewa, listrik, gas, packaging, dan overhead cabang.',
    'actionRoute' => route('admin.biaya.create'),
    'actionLabel' => 'Catat Biaya Baru',
])

{{-- Summary Card --}}
<div class="mb-6 grid gap-4 sm:grid-cols-3">
    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-xs">
        <p class="text-xs font-semibold uppercase text-slate-500">Total Pengeluaran (Tercatat)</p>
        <p class="mt-2 text-2xl font-black text-rose-600 font-mono">Rp {{ number_format($expenses->sum('amount'), 0, ',', '.') }}</p>
    </div>
</div>

<div class="overflow-x-auto rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white shadow-xs">
    <table class="w-full border-collapse text-left text-sm">
        <thead>
            <tr class="border-b border-[var(--color-paper-3)] bg-[var(--color-paper-2)] font-semibold text-[var(--color-ink)]">
                <th class="px-6 py-4">Tanggal</th>
                <th class="px-6 py-4">Cabang</th>
                <th class="px-6 py-4">Kategori Biaya</th>
                <th class="px-6 py-4">Deskripsi / Keterangan</th>
                <th class="px-6 py-4">Metode Bayar</th>
                <th class="px-6 py-4 text-right">Jumlah Biaya</th>
                <th class="px-6 py-4 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[var(--color-paper-3)] text-[var(--color-ink-2)]">
            @forelse($expenses as $exp)
                <tr class="hover:bg-[var(--color-paper-2)] transition-colors">
                    <td class="px-6 py-4 font-mono text-xs text-slate-600">{{ $exp->expense_date->format('d/m/Y') }}</td>
                    <td class="px-6 py-4 font-medium text-slate-800">{{ $exp->branch?->name ?? 'Headquarters' }}</td>
                    <td class="px-6 py-4">
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold capitalize text-slate-700">
                            {{ str_replace('_', ' ', $exp->category) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-xs text-slate-700 max-w-xs truncate">{{ $exp->description }}</td>
                    <td class="px-6 py-4 text-xs uppercase font-mono text-slate-500">{{ $exp->payment_method ?? 'CASH' }}</td>
                    <td class="px-6 py-4 text-right font-mono font-bold text-rose-600">Rp {{ number_format($exp->amount, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.biaya.edit', $exp) }}" class="rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-1.5 text-xs font-medium text-[var(--color-accent)] hover:bg-[var(--color-accent-light)]">Edit</a>
                            <form method="POST" action="{{ route('admin.biaya.destroy', $exp) }}" onsubmit="return confirm('Hapus pencatatan biaya ini?')">
                                @csrf @method('DELETE')
                                <button class="rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-1.5 text-xs font-medium text-[var(--color-danger)] hover:bg-red-50">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-6 py-10 text-center text-[var(--color-ink-3)]">Belum ada pencatatan biaya operasional.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($expenses->hasPages())
    <div class="mt-6">{{ $expenses->links() }}</div>
@endif
@endsection
