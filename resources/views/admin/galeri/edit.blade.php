@extends('admin.layouts.app')

@section('title', 'Edit Galeri — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Edit Galeri',
    'description' => 'Perbarui data galeri.',
    'actionRoute' => route('admin.galeri.index'),
    'actionLabel' => 'Kembali',
])

<div class="max-w-3xl rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white p-6 sm:p-8">
    <form method="POST" action="{{ route('admin.galeri.update', $galeri) }}" class="space-y-6">
        @csrf
        @method('PUT')
        <div class="grid gap-6 sm:grid-cols-2"><div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">URL Gambar</label>
            <input type="text" name="image" required value="{{ old('image', $galeri->image) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
</div>
<div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Keterangan</label>
            <input type="text" name="caption" value="{{ old('caption', $galeri->caption) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
</div>
<div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Urutan</label>
            <input type="number" name="ord" min="0" value="{{ old('ord', $galeri->ord) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
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
