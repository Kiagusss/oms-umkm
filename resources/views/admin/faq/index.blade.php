@extends('admin.layouts.app')

@section('title', 'FAQ — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'FAQ',
    'description' => 'Kelola data faq.',
    'actionRoute' => route('admin.faq.create'),
    'actionLabel' => 'Tambah FAQ',
])

<div class="overflow-x-auto rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white">
    <table class="w-full border-collapse text-left text-sm">
        <thead>
            <tr class="border-b border-[var(--color-paper-3)] bg-[var(--color-paper-2)] font-semibold text-[var(--color-ink)]">
                <th class="px-6 py-4">Pertanyaan</th><th class="px-6 py-4">Status</th><th class="px-6 py-4 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[var(--color-paper-3)] text-[var(--color-ink-2)]">
            @forelse($faqs as $item)
                <tr class="hover:bg-[var(--color-paper-2)] transition-colors">
                    <td class="px-6 py-4 font-medium text-[var(--color-ink)]">{{ Str::limit($item->question, 50) }}</td><td class="px-6 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $item->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">{{ $item->status }}</span></td>
                    <td class="px-6 py-4 text-right"><div class="flex justify-end gap-2"><a href="{{ route('admin.faq.edit', $item) }}" class="rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-1.5 text-xs font-medium text-[var(--color-accent)] hover:bg-[var(--color-accent-light)]">Edit</a><form method="POST" action="{{ route('admin.faq.destroy', $item) }}" onsubmit="return confirm('Hapus FAQ ini?')">@csrf @method('DELETE')<button class="rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-1.5 text-xs font-medium text-[var(--color-danger)] hover:bg-red-50">Hapus</button></form></div></td>
                </tr>
            @empty
                <tr><td colspan="3" class="px-6 py-10 text-center text-[var(--color-ink-3)]">Belum ada faq.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
