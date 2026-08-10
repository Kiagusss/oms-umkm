@extends('admin.layouts.app')

@section('title', 'Galeri — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Galeri',
    'description' => 'Kelola data galeri.',
    'actionRoute' => route('admin.galeri.create'),
    'actionLabel' => 'Tambah Galeri',
])

<div class="overflow-x-auto rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white">
    <table class="w-full border-collapse text-left text-sm">
        <thead>
            <tr class="border-b border-[var(--color-paper-3)] bg-[var(--color-paper-2)] font-semibold text-[var(--color-ink)]">
                <th class="px-6 py-4">URL Gambar</th><th class="px-6 py-4">Keterangan</th><th class="px-6 py-4 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[var(--color-paper-3)] text-[var(--color-ink-2)]">
            @forelse($gallery as $item)
                <tr class="hover:bg-[var(--color-paper-2)] transition-colors">
                    <td class="px-6 py-4"><img src="{{ asset($item->image) }}" class="h-11 w-11 rounded-[var(--radius-md)] object-cover" alt=""></td><td class="px-6 py-4">{{ $item->caption }}</td>
                    <td class="px-6 py-4 text-right"><div class="flex justify-end gap-2"><a href="{{ route('admin.galeri.edit', $item) }}" class="rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-1.5 text-xs font-medium text-[var(--color-accent)] hover:bg-[var(--color-accent-light)]">Edit</a><form method="POST" action="{{ route('admin.galeri.destroy', $item) }}" onsubmit="return confirm('Hapus Galeri ini?')">@csrf @method('DELETE')<button class="rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-1.5 text-xs font-medium text-[var(--color-danger)] hover:bg-red-50">Hapus</button></form></div></td>
                </tr>
            @empty
                <tr><td colspan="3" class="px-6 py-10 text-center text-[var(--color-ink-3)]">Belum ada galeri.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
