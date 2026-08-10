@extends('admin.layouts.app')

@section('title', 'Tambah Produk — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Tambah Produk',
    'description' => 'Lengkapi detail menu pempek baru.',
    'actionRoute' => route('admin.produk.index'),
    'actionLabel' => 'Kembali',
])

<div class="max-w-3xl rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white p-6 sm:p-8">
    <form method="POST" action="{{ route('admin.produk.store') }}" class="space-y-6">
        @csrf
        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Nama Produk</label>
                <input type="text" name="name" required value="{{ old('name') }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Slug</label>
                <input type="text" name="slug" required value="{{ old('slug') }}" placeholder="pempek-lenjer" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
        </div>
        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Harga (Rp)</label>
                <input type="number" name="price" required min="0" value="{{ old('price') }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Harga Coret (Rp, opsional)</label>
                <input type="number" name="price_strikethrough" min="0" value="{{ old('price_strikethrough') }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
        </div>
        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Kategori</label>
                <select name="category_id" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
                    <option value="">Tanpa Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Stok</label>
                <input type="number" name="stock" required min="0" value="{{ old('stock', 0) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
        </div>
        <div>
            <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Deskripsi Singkat</label>
            <textarea name="short_description" rows="2" required class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">{{ old('short_description') }}</textarea>
        </div>
        <div>
            <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Deskripsi Lengkap</label>
            <textarea name="description" rows="4" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">{{ old('description') }}</textarea>
        </div>
        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Komposisi</label>
                <input type="text" name="composition" value="{{ old('composition') }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Berat (gram)</label>
                <input type="number" name="weight" min="0" value="{{ old('weight', 0) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
        </div>
        <div>
            <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">URL Gambar Utama</label>
            <input type="text" name="thumbnail" value="{{ old('thumbnail') }}" placeholder="/images/products/lenjer.jpg" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
        </div>
        <div>
            <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Gambar Galeri (pisahkan dengan koma)</label>
            <input type="text" name="images_list" value="{{ old('images_list') }}" placeholder="/images/products/a.jpg, /images/products/b.jpg" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
        </div>
        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Status</label>
                <select name="status" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
                    <option value="active" @selected(old('status', 'active') === 'active')>Aktif</option>
                    <option value="inactive" @selected(old('status') === 'inactive')>Nonaktif</option>
                </select>
            </div>
            <div class="flex items-end gap-4 pb-2">
                <label class="flex items-center gap-2 text-sm text-[var(--color-ink-2)]">
                    <input type="checkbox" name="is_best_seller" value="1" @checked(old('is_best_seller'))> Best seller
                </label>
                <label class="flex items-center gap-2 text-sm text-[var(--color-ink-2)]">
                    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured'))> Unggulan
                </label>
            </div>
        </div>

        <div class="flex justify-end border-t border-[var(--color-paper-3)] pt-6">
            <button type="submit" class="rounded-[var(--radius-xl)] bg-[var(--color-accent)] px-6 py-2.5 text-sm font-semibold text-white transition-all hover:bg-[var(--color-accent-hover)]">
                Simpan Produk
            </button>
        </div>
    </form>
</div>
@endsection
