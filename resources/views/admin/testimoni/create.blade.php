@extends('admin.layouts.app')

@section('title', 'Tambah Testimoni — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Tambah Testimoni',
    'description' => 'Lengkapi data testimoni.',
    'actionRoute' => route('admin.testimoni.index'),
    'actionLabel' => 'Kembali',
])

<div class="max-w-3xl rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white p-6 sm:p-8">
    <form method="POST" action="{{ route('admin.testimoni.store') }}" class="space-y-6">
        @csrf
        
        <div class="grid gap-6 sm:grid-cols-2"><div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Nama</label>
            <input type="text" name="name" required value="{{ old('name', '') }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
</div>
<div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">URL Avatar</label>
            <input type="text" name="avatar" value="{{ old('avatar', '') }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
</div>
<div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Kombinasi</label>
            <input type="text" name="kombinasi" value="{{ old('kombinasi', '') }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
</div>
<div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Rating (1-5)</label>
            <input type="number" name="rating" min="0" value="{{ old('rating', '') }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
</div>
    </div>
    <div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Komentar</label>
            <textarea name="comment" rows="4" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">{{ old('comment', '') }}</textarea>
</div>
    <div class="grid gap-6 sm:grid-cols-2"><div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Tanggal</label>
            <input type="date" name="date" value="{{ old('date', '') }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
</div>
<div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Status</label>
            <select name="status" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm"><option value="active" @selected(old('status', '') === 'active')>Aktif</option><option value="inactive" @selected(old('status', '') === 'inactive')>Nonaktif</option></select>
</div>
<div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Urutan</label>
            <input type="number" name="ord" min="0" value="{{ old('ord', '') }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
</div>
    </div>

        <div class="flex justify-end border-t border-[var(--color-paper-3)] pt-6">
            <button type="submit" class="rounded-[var(--radius-xl)] bg-[var(--color-accent)] px-6 py-2.5 text-sm font-semibold text-white transition-all hover:bg-[var(--color-accent-hover)]">
                Simpan
            </button>
        </div>
    </form>
</div>
@endsection
