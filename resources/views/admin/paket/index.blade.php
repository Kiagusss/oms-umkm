@extends('admin.layouts.app')

@section('title', 'Paket — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Paket',
    'description' => 'Kelola data paket.',
    'actionRoute' => route('admin.paket.create'),
    'actionLabel' => 'Tambah Paket',
])

<div class="overflow-x-auto rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white">
    <table class="w-full border-collapse text-left text-sm">
        <thead>
            <tr class="border-b border-[var(--color-paper-3)] bg-[var(--color-paper-2)] font-semibold text-[var(--color-ink)]">
                <th class="px-6 py-4">Nama</th><th class="px-6 py-4">Harga (Rp)</th><th class="px-6 py-4">Badge</th><th class="px-6 py-4">Status</th><th class="px-6 py-4 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[var(--color-paper-3)] text-[var(--color-ink-2)]">
            @forelse($packages as $item)
                <tr class="hover:bg-[var(--color-paper-2)] transition-colors">
                    <td class="px-6 py-4">{{ $item->name }}</td><td class="px-6 py-4 font-semibold tabular-nums text-[var(--color-ink)]">Rp{{ number_format($item->price) }}</td><td class="px-6 py-4">{{ $item->badge }}</td><td class="px-6 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $item->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">{{ $item->status }}</span></td>
                    <td class="px-6 py-4 text-right"><div class="flex justify-end gap-2"><a href="{{ route('admin.paket.edit', $item) }}" class="rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-1.5 text-xs font-medium text-[var(--color-accent)] hover:bg-[var(--color-accent-light)]">Edit</a><form method="POST" action="{{ route('admin.paket.destroy', $item) }}" onsubmit="return confirm('Hapus Paket ini?')">@csrf @method('DELETE')<button class="rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-1.5 text-xs font-medium text-[var(--color-danger)] hover:bg-red-50">Hapus</button></form></div></td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-6 py-10 text-center text-[var(--color-ink-3)]">Belum ada paket.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
