@extends('admin.layouts.app')

@section('title', 'Edit Paket — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Edit Paket',
    'description' => 'Perbarui data paket.',
    'actionRoute' => route('admin.paket.index'),
    'actionLabel' => 'Kembali',
])

<div class="max-w-3xl rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white p-6 sm:p-8">
    <form method="POST" action="{{ route('admin.paket.update', $paket) }}" class="space-y-6">
        @csrf
        @method('PUT')
        <div class="grid gap-6 sm:grid-cols-2"><div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Nama</label>
            <input type="text" name="name" required value="{{ old('name', $paket->name) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
</div>
    </div>
    <div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Deskripsi</label>
            <textarea name="description" rows="4" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">{{ old('description', $paket->description) }}</textarea>
</div>
    <div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Isi Paket</label>
            <textarea name="items" rows="4" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">{{ old('items', $paket->items) }}</textarea>
</div>
    <div class="grid gap-6 sm:grid-cols-2"><div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Harga (Rp)</label>
            <input type="number" name="price" min="0" value="{{ old('price', $paket->price) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
</div>
<div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Harga Coret (Rp)</label>
            <input type="number" name="original_price" min="0" value="{{ old('original_price', $paket->original_price) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
</div>
<div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Badge</label>
            <input type="text" name="badge" value="{{ old('badge', $paket->badge) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
</div>
    </div>
    <div>
    <label class="flex items-center gap-2 text-sm text-[var(--color-ink-2)]">
            <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $paket->is_featured))> Unggulan
        </label>
</div>
    <div class="grid gap-6 sm:grid-cols-2"><div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Urutan</label>
            <input type="number" name="ord" min="0" value="{{ old('ord', $paket->ord) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
</div>
<div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Status</label>
            <select name="status" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm"><option value="active" @selected(old('status', $paket->status) === 'active')>Aktif</option><option value="inactive" @selected(old('status', $paket->status) === 'inactive')>Nonaktif</option></select>
</div>
    </div>

        <div class="flex justify-end border-t border-[var(--color-paper-3)] pt-6">
            <button type="submit" class="rounded-[var(--radius-xl)] bg-[var(--color-accent)] px-6 py-2.5 text-sm font-semibold text-white transition-all hover:bg-[var(--color-accent-hover)]">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
