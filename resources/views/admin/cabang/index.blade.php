@extends('admin.layouts.app')

@section('title', 'Cabang — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Manajemen Cabang',
    'description' => 'Kelola cabang fisik / outlet toko pempek.',
    'actionRoute' => route('admin.cabang.create'),
    'actionLabel' => 'Tambah Cabang',
])

<div class="overflow-x-auto rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white">
    <table class="w-full border-collapse text-left text-sm">
        <thead>
            <tr class="border-b border-[var(--color-paper-3)] bg-[var(--color-paper-2)] font-semibold text-[var(--color-ink)]">
                <th class="px-6 py-4">Kode</th>
                <th class="px-6 py-4">Nama Cabang</th>
                <th class="px-6 py-4">Alamat</th>
                <th class="px-6 py-4">Telepon</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[var(--color-paper-3)] text-[var(--color-ink-2)]">
            @forelse($branches as $branch)
                <tr class="hover:bg-[var(--color-paper-2)] transition-colors">
                    <td class="px-6 py-4 font-mono font-semibold text-[var(--color-ink)]">{{ $branch->code }}</td>
                    <td class="px-6 py-4 font-semibold text-[var(--color-ink)]">{{ $branch->name }}</td>
                    <td class="px-6 py-4 text-xs text-slate-500">{{ $branch->address ?? '—' }}</td>
                    <td class="px-6 py-4 text-xs">{{ $branch->phone ?? '—' }}</td>
                    <td class="px-6 py-4">
                        <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $branch->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $branch->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.cabang.edit', $branch) }}" class="rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-1.5 text-xs font-medium text-[var(--color-accent)] hover:bg-[var(--color-accent-light)]">Edit</a>
                            <form method="POST" action="{{ route('admin.cabang.destroy', $branch) }}" onsubmit="return confirm('Hapus cabang {{ $branch->name }}?')">
                                @csrf @method('DELETE')
                                <button class="rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-1.5 text-xs font-medium text-[var(--color-danger)] hover:bg-red-50">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-6 py-10 text-center text-[var(--color-ink-3)]">Belum ada cabang terdaftar. Klik "Tambah Cabang" untuk membuat.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($branches->hasPages())
    <div class="mt-6">{{ $branches->links() }}</div>
@endif
@endsection
