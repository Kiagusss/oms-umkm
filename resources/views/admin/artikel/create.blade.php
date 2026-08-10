@extends('admin.layouts.app')

@section('title', 'Tambah Artikel — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Tambah Artikel',
    'description' => 'Lengkapi data artikel.',
    'actionRoute' => route('admin.artikel.index'),
    'actionLabel' => 'Kembali',
])

<div class="max-w-3xl rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white p-6 sm:p-8">
    <form method="POST" action="{{ route('admin.artikel.store') }}" class="space-y-6">
        @csrf
        
        <div class="grid gap-6 sm:grid-cols-2"><div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Judul</label>
            <input type="text" name="title" required value="{{ old('title', '') }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
</div>
<div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Slug</label>
            <input type="text" name="slug" required value="{{ old('slug', '') }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
</div>
<div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Kategori Artikel</label>
            <input type="text" name="category" value="{{ old('category', '') }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
</div>
<div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">URL Gambar</label>
            <input type="text" name="thumbnail" value="{{ old('thumbnail', '') }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
</div>
<div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Penulis</label>
            <input type="text" name="author" value="{{ old('author', '') }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
</div>
<div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Tanggal</label>
            <input type="date" name="date" value="{{ old('date', '') }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
</div>
    </div>
    <div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Konten (HTML)</label>
            <textarea name="content" rows="4" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">{{ old('content', '') }}</textarea>
</div>
    <div class="grid gap-6 sm:grid-cols-2"><div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">SEO Title</label>
            <input type="text" name="seo_title" value="{{ old('seo_title', '') }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
</div>
<div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">SEO Description</label>
            <input type="text" name="seo_description" value="{{ old('seo_description', '') }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
</div>
<div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Meta Keywords</label>
            <input type="text" name="meta_keywords" value="{{ old('meta_keywords', '') }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
</div>
<div>
    <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Status</label>
            <select name="status" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm"><option value="draft" @selected(old('status', '') === 'draft')>Draft</option><option value="published" @selected(old('status', '') === 'published')>Terbit</option></select>
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
