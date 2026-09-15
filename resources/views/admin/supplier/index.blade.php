@extends('admin.layouts.app')

@section('title', 'Supplier — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Manajemen Supplier',
    'description' => 'Kelola vendor dan pemasok bahan baku & packaging.',
    'actionRoute' => route('admin.supplier.create'),
    'actionLabel' => 'Tambah Supplier',
])

<div class="overflow-x-auto rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white shadow-xs">
    <table class="w-full border-collapse text-left text-sm">
        <thead>
            <tr class="border-b border-[var(--color-paper-3)] bg-[var(--color-paper-2)] font-semibold text-[var(--color-ink)]">
                <th class="px-6 py-4">Kode</th>
                <th class="px-6 py-4">Nama Supplier</th>
                <th class="px-6 py-4">Kontak Person</th>
                <th class="px-6 py-4">Telepon / HP</th>
                <th class="px-6 py-4">Email</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[var(--color-paper-3)] text-[var(--color-ink-2)]">
            @forelse($suppliers as $supplier)
                <tr class="hover:bg-[var(--color-paper-2)] transition-colors">
                    <td class="px-6 py-4 font-mono font-semibold text-[var(--color-ink)]">{{ $supplier->code }}</td>
                    <td class="px-6 py-4 font-semibold text-[var(--color-ink)]">{{ $supplier->name }}</td>
                    <td class="px-6 py-4 text-xs">{{ $supplier->contact_person ?? '—' }}</td>
                    <td class="px-6 py-4 text-xs">{{ $supplier->phone ?? '—' }}</td>
                    <td class="px-6 py-4 text-xs">{{ $supplier->email ?? '—' }}</td>
                    <td class="px-6 py-4">
                        <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $supplier->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $supplier->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.supplier.edit', $supplier) }}" class="rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-1.5 text-xs font-medium text-[var(--color-accent)] hover:bg-[var(--color-accent-light)]">Edit</a>
                            <form method="POST" action="{{ route('admin.supplier.destroy', $supplier) }}" onsubmit="return confirm('Hapus supplier {{ $supplier->name }}?')">
                                @csrf @method('DELETE')
                                <button class="rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-1.5 text-xs font-medium text-[var(--color-danger)] hover:bg-red-50">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-6 py-10 text-center text-[var(--color-ink-3)]">Belum ada supplier terdaftar. Klik "Tambah Supplier" untuk membuat.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($suppliers->hasPages())
    <div class="mt-6">{{ $suppliers->links() }}</div>
@endif
@endsection
