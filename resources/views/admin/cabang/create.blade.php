@extends('admin.layouts.app')

@section('title', 'Tambah Cabang — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Tambah Cabang',
    'description' => 'Daftarkan cabang atau outlet baru.',
    'actionRoute' => route('admin.cabang.index'),
    'actionLabel' => 'Kembali',
])

<div class="max-w-2xl rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white p-6 sm:p-8">
    <form method="POST" action="{{ route('admin.cabang.store') }}" class="space-y-5">
        @csrf
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Kode Cabang</label>
                <input type="text" name="code" required value="{{ old('code') }}" placeholder="Misal: CBG-JKT01" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm uppercase">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Nama Cabang</label>
                <input type="text" name="name" required value="{{ old('name') }}" placeholder="Misal: Outlet Tebet" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Alamat Lengkap</label>
            <textarea name="address" rows="3" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm" placeholder="Alamat cabang">{{ old('address') }}</textarea>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Nomor Telepon</label>
                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="0812xxxx" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
            <div class="flex items-end pb-2">
                <label class="flex items-center gap-2 text-sm font-medium text-[var(--color-ink-2)]">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="rounded border-slate-300 text-emerald-600">
                    Cabang Aktif Beroperasi
                </label>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
            <a href="{{ route('admin.cabang.index') }}" class="rounded-[var(--radius-md)] border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</a>
            <button type="submit" class="rounded-[var(--radius-md)] bg-[var(--color-accent)] px-5 py-2 text-sm font-semibold text-white hover:bg-[var(--color-accent-hover)]">Simpan Cabang</button>
        </div>
    </form>
</div>
@endsection
