@extends('admin.layouts.app')

@section('title', 'Manajemen Pengguna & Hak Akses — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Manajemen Pengguna & Staf',
    'description' => 'Kelola akun karyawan, penetapan cabang kerja, dan hak akses sistem (RBAC).',
    'actionRoute' => route('admin.pengguna.create'),
    'actionLabel' => 'Tambah Staf Baru',
])

<div class="overflow-x-auto rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white shadow-xs">
    <table class="w-full border-collapse text-left text-sm">
        <thead>
            <tr class="border-b border-[var(--color-paper-3)] bg-[var(--color-paper-2)] font-semibold text-[var(--color-ink)]">
                <th class="px-6 py-4">Nama Lengkap</th>
                <th class="px-6 py-4">Email</th>
                <th class="px-6 py-4">Role / Peran</th>
                <th class="px-6 py-4">Cabang Penugasan</th>
                <th class="px-6 py-4">Terdaftar</th>
                <th class="px-6 py-4 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[var(--color-paper-3)] text-[var(--color-ink-2)]">
            @forelse($users as $u)
                @php
                    $roleName = $u->role?->name ?? 'owner';
                    $roleLabel = $u->role?->label ?? 'Owner';
                    $roleColors = [
                        'owner' => 'bg-purple-50 text-purple-700 border-purple-200',
                        'manager' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'cashier' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'warehouse' => 'bg-slate-100 text-slate-700 border-slate-200',
                    ];
                    $badgeClass = $roleColors[$roleName] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                @endphp
                <tr class="hover:bg-[var(--color-paper-2)] transition-colors">
                    <td class="px-6 py-4 font-semibold text-[var(--color-ink)] flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center font-bold text-xs text-slate-700 uppercase">
                            {{ substr($u->name, 0, 2) }}
                        </div>
                        {{ $u->name }}
                    </td>
                    <td class="px-6 py-4 font-mono text-xs">{{ $u->email }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-block rounded-full border px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wider {{ $badgeClass }}">
                            {{ $roleLabel }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-xs font-semibold text-slate-700">
                        {{ $u->branch?->name ?? 'Semua Cabang (HQ)' }}
                    </td>
                    <td class="px-6 py-4 font-mono text-xs text-slate-500">{{ $u->created_at->format('d/m/Y') }}</td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.pengguna.edit', $u) }}" class="rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-1.5 text-xs font-medium text-[var(--color-accent)] hover:bg-[var(--color-accent-light)]">Edit</a>
                            @if(auth()->id() !== $u->id)
                                <form method="POST" action="{{ route('admin.pengguna.destroy', $u) }}" onsubmit="return confirm('Hapus staf {{ $u->name }}?')">
                                    @csrf @method('DELETE')
                                    <button class="rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-1.5 text-xs font-medium text-[var(--color-danger)] hover:bg-red-50">Hapus</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-6 py-10 text-center text-[var(--color-ink-3)]">Belum ada staf terdaftar.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($users->hasPages())
    <div class="mt-6">{{ $users->links() }}</div>
@endif
@endsection
