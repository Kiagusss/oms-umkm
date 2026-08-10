@extends('admin.layouts.app')

@section('title', 'Testimoni — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Testimoni',
    'description' => 'Kelola data testimoni.',
    'actionRoute' => route('admin.testimoni.create'),
    'actionLabel' => 'Tambah Testimoni',
])

<div class="overflow-x-auto rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white">
    <table class="w-full border-collapse text-left text-sm">
        <thead>
            <tr class="border-b border-[var(--color-paper-3)] bg-[var(--color-paper-2)] font-semibold text-[var(--color-ink)]">
                <th class="px-6 py-4">Nama</th><th class="px-6 py-4">Kombinasi</th><th class="px-6 py-4">Rating (1-5)</th><th class="px-6 py-4">Status</th><th class="px-6 py-4 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[var(--color-paper-3)] text-[var(--color-ink-2)]">
            @forelse($testimonials as $item)
                <tr class="hover:bg-[var(--color-paper-2)] transition-colors">
                    <td class="px-6 py-4">{{ $item->name }}</td><td class="px-6 py-4">{{ $item->kombinasi }}</td><td class="px-6 py-4 tabular-nums">{{ $item->rating }}★</td><td class="px-6 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $item->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">{{ $item->status }}</span></td>
                    <td class="px-6 py-4 text-right"><div class="flex justify-end gap-2"><a href="{{ route('admin.testimoni.edit', $item) }}" class="rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-1.5 text-xs font-medium text-[var(--color-accent)] hover:bg-[var(--color-accent-light)]">Edit</a><form method="POST" action="{{ route('admin.testimoni.destroy', $item) }}" onsubmit="return confirm('Hapus Testimoni ini?')">@csrf @method('DELETE')<button class="rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-1.5 text-xs font-medium text-[var(--color-danger)] hover:bg-red-50">Hapus</button></form></div></td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-6 py-10 text-center text-[var(--color-ink-3)]">Belum ada testimoni.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
