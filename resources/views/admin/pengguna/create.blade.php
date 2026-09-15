@extends('admin.layouts.app')

@section('title', 'Tambah Staf — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Tambah Staf Baru',
    'description' => 'Buat akun pengguna dan tentukan hak akses peran serta cabang penugasan.',
    'actionRoute' => route('admin.pengguna.index'),
    'actionLabel' => 'Kembali',
])

<div class="max-w-2xl rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white p-6 sm:p-8">
    <form method="POST" action="{{ route('admin.pengguna.store') }}" class="space-y-5">
        @csrf
        <div>
            <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Nama Lengkap Karyawan</label>
            <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Siti Rahma" required class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Alamat Email Login</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="siti@pempek.com" required class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Password Awal</label>
                <input type="password" name="password" placeholder="Minimal 8 karakter" required class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Role / Hak Akses</label>
                <select name="role_id" required class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm font-semibold capitalize">
                    @foreach($roles as $r)
                        <option value="{{ $r->id }}">{{ $r->label ?? $r->name }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-slate-500">Owner memiliki hak akses penuh, Kasir terbatas pada POS & transaksi.</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Cabang Penugasan</label>
                <select name="branch_id" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm font-semibold">
                    <option value="">Semua Cabang / HQ Pusat</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-slate-500">Kasir/Dapur akan otomatis terikat ke stok cabang ini.</p>
            </div>
        </div>

        <div class="pt-3 flex justify-end gap-3">
            <a href="{{ route('admin.pengguna.index') }}" class="rounded-[var(--radius-md)] border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</a>
            <button type="submit" class="rounded-[var(--radius-md)] bg-[var(--color-accent)] px-5 py-2 text-sm font-semibold text-white hover:bg-[var(--color-accent-hover)]">Daftarkan Staf</button>
        </div>
    </form>
</div>
@endsection
