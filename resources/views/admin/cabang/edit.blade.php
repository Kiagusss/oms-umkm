@extends('admin.layouts.app')

@section('title', 'Edit Cabang — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Edit Cabang',
    'description' => 'Perbarui data cabang atau outlet.',
    'actionRoute' => route('admin.cabang.index'),
    'actionLabel' => 'Kembali',
])

<div class="max-w-2xl rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white p-6 sm:p-8">
    <form method="POST" action="{{ route('admin.cabang.update', $branch) }}" class="space-y-5">
        @csrf
        @method('PUT')
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Kode Cabang</label>
                <input type="text" name="code" required value="{{ old('code', $branch->code) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm uppercase">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Nama Cabang</label>
                <input type="text" name="name" required value="{{ old('name', $branch->name) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Alamat Lengkap</label>
            <textarea name="address" rows="3" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">{{ old('address', $branch->address) }}</textarea>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Nomor Telepon</label>
                <input type="text" name="phone" value="{{ old('phone', $branch->phone) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
            <div class="flex items-end pb-2">
                <label class="flex items-center gap-2 text-sm font-medium text-[var(--color-ink-2)]">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $branch->is_active)) class="rounded border-slate-300 text-emerald-600">
                    Cabang Aktif Beroperasi
                </label>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
            <a href="{{ route('admin.cabang.index') }}" class="rounded-[var(--radius-md)] border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</a>
            <button type="submit" class="rounded-[var(--radius-md)] bg-[var(--color-accent)] px-5 py-2 text-sm font-semibold text-white hover:bg-[var(--color-accent-hover)]">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection
