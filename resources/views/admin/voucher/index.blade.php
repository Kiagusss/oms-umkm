@extends('admin.layouts.app')

@section('title', 'Voucher — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Voucher',
    'description' => 'Kelola voucher diskon untuk kasir (POS).',
    'actionRoute' => route('admin.voucher.create'),
    'actionLabel' => 'Tambah Voucher',
])

<div class="overflow-x-auto rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white">
    <table class="w-full border-collapse text-left text-sm">
        <thead>
            <tr class="border-b border-[var(--color-paper-3)] bg-[var(--color-paper-2)] font-semibold text-[var(--color-ink)]">
                <th class="px-6 py-4">Kode</th>
                <th class="px-6 py-4">Diskon</th>
                <th class="px-6 py-4">Min. Order</th>
                <th class="px-6 py-4">Maks. Pakai</th>
                <th class="px-6 py-4">Berlaku</th>
                <th class="px-6 py-4">Dipakai</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[var(--color-paper-3)] text-[var(--color-ink-2)]">
            @forelse($vouchers as $voucher)
                <tr class="hover:bg-[var(--color-paper-2)] transition-colors">
                    <td class="px-6 py-4 font-mono font-semibold text-[var(--color-ink)]">{{ $voucher->code }}</td>
                    <td class="px-6 py-4 font-semibold text-[var(--color-accent)]">
                        {{ $voucher->type === 'percentage' ? number_format($voucher->value, 0, ',', '.') . '%' : 'Rp ' . number_format($voucher->value, 0, ',', '.') }}
                    </td>
                    <td class="px-6 py-4 tabular-nums">{{ $voucher->min_order !== null ? 'Rp ' . number_format($voucher->min_order, 0, ',', '.') : '—' }}</td>
                    <td class="px-6 py-4 tabular-nums">{{ $voucher->max_uses ?? '—' }}</td>
                    <td class="px-6 py-4 text-xs">
                        @if($voucher->valid_from || $voucher->valid_until)
                            @if($voucher->valid_from) {{ $voucher->valid_from->translatedFormat('d M Y') }} @endif
                            s/d
                            @if($voucher->valid_until) {{ $voucher->valid_until->translatedFormat('d M Y') }} @endif
                        @else
                            <span class="text-[var(--color-ink-3)]">Selalu</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 tabular-nums">{{ $voucher->used_count }}</td>
                    <td class="px-6 py-4">
                        <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $voucher->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $voucher->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.voucher.edit', $voucher) }}" class="rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-1.5 text-xs font-medium text-[var(--color-accent)] hover:bg-[var(--color-accent-light)]">Edit</a>
                            <form method="POST" action="{{ route('admin.voucher.destroy', $voucher) }}" onsubmit="return confirm('Hapus Voucher ini?')">
                                @csrf @method('DELETE')
                                <button class="rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-1.5 text-xs font-medium text-[var(--color-danger)] hover:bg-red-50">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="px-6 py-10 text-center text-[var(--color-ink-3)]">Belum ada voucher. Klik "Tambah Voucher" untuk membuat.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($vouchers->hasPages())
    <div class="mt-6">{{ $vouchers->links() }}</div>
@endif
@endsection
