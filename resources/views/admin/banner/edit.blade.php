@extends('admin.layouts.app')

@section('title', 'Edit Banner — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Edit Banner',
    'description' => 'Perbarui data banner.',
    'actionRoute' => route('admin.banner.index'),
    'actionLabel' => 'Kembali',
])

<div class="max-w-3xl rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white p-6 sm:p-8">
    <form method="POST" action="{{ route('admin.banner.update', $banner) }}" class="space-y-6">
        @csrf
        @method('PUT')
        <div class="grid gap-6 sm:grid-cols-2"><div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Judul</label>
            <input type="text" name="title" required value="{{ old('title', $banner->title) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
</div>
<div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Subjudul</label>
            <input type="text" name="subtitle" value="{{ old('subtitle', $banner->subtitle) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
</div>
<div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Teks Tombol</label>
            <input type="text" name="button_text" value="{{ old('button_text', $banner->button_text) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
</div>
<div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Link Tombol</label>
            <input type="text" name="button_link" value="{{ old('button_link', $banner->button_link) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
</div>
<div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">URL Gambar Latar</label>
            <input type="text" name="background_image" required value="{{ old('background_image', $banner->background_image) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
</div>
<div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Status</label>
            <select name="status" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm"><option value="active" @selected(old('status', $banner->status) === 'active')>Aktif</option><option value="inactive" @selected(old('status', $banner->status) === 'inactive')>Nonaktif</option></select>
</div>
<div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Urutan</label>
            <input type="number" name="ord" min="0" value="{{ old('ord', $banner->ord) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
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
