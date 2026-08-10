@extends('admin.layouts.app')

@section('title', 'Banner — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Banner',
    'description' => 'Kelola data banner.',
    'actionRoute' => route('admin.banner.create'),
    'actionLabel' => 'Tambah Banner',
])

<div class="overflow-x-auto rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white">
    <table class="w-full border-collapse text-left text-sm">
        <thead>
            <tr class="border-b border-[var(--color-paper-3)] bg-[var(--color-paper-2)] font-semibold text-[var(--color-ink)]">
                <th class="px-6 py-4">Judul</th><th class="px-6 py-4">Subjudul</th><th class="px-6 py-4">Status</th><th class="px-6 py-4 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[var(--color-paper-3)] text-[var(--color-ink-2)]">
            @forelse($banners as $item)
                <tr class="hover:bg-[var(--color-paper-2)] transition-colors">
                    <td class="px-6 py-4">{{ $item->title }}</td><td class="px-6 py-4">{{ $item->subtitle }}</td><td class="px-6 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $item->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">{{ $item->status }}</span></td>
                    <td class="px-6 py-4 text-right"><div class="flex justify-end gap-2"><a href="{{ route('admin.banner.edit', $item) }}" class="rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-1.5 text-xs font-medium text-[var(--color-accent)] hover:bg-[var(--color-accent-light)]">Edit</a><form method="POST" action="{{ route('admin.banner.destroy', $item) }}" onsubmit="return confirm('Hapus Banner ini?')">@csrf @method('DELETE')<button class="rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-1.5 text-xs font-medium text-[var(--color-danger)] hover:bg-red-50">Hapus</button></form></div></td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-6 py-10 text-center text-[var(--color-ink-3)]">Belum ada banner.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
