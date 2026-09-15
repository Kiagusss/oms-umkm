@extends('admin.layouts.app')

@section('title', 'Edit Staf — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Edit Data Staf: ' . $user->name,
    'description' => 'Perbarui data profil, peran hak akses, atau pindah cabang penugasan.',
    'actionRoute' => route('admin.pengguna.index'),
    'actionLabel' => 'Kembali',
])

<div class="max-w-2xl rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white p-6 sm:p-8">
    <form method="POST" action="{{ route('admin.pengguna.update', $user) }}" class="space-y-5">
        @csrf
        @method('PUT')
        <div>
            <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Nama Lengkap Karyawan</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Alamat Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Ganti Password (Opsional)</label>
                <input type="password" name="password" placeholder="Kosongkan jika tidak diubah" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Role / Hak Akses</label>
                <select name="role_id" required class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm font-semibold capitalize">
                    @foreach($roles as $r)
                        <option value="{{ $r->id }}" @selected(old('role_id', $user->role_id) == $r->id)>{{ $r->label ?? $r->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Cabang Penugasan</label>
                <select name="branch_id" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm font-semibold">
                    <option value="">Semua Cabang / HQ Pusat</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" @selected(old('branch_id', $user->branch_id) == $b->id)>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="pt-3 flex justify-end gap-3">
            <a href="{{ route('admin.pengguna.index') }}" class="rounded-[var(--radius-md)] border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</a>
            <button type="submit" class="rounded-[var(--radius-md)] bg-[var(--color-accent)] px-5 py-2 text-sm font-semibold text-white hover:bg-[var(--color-accent-hover)]">Perbarui Data Staf</button>
        </div>
    </form>
</div>
@endsection
