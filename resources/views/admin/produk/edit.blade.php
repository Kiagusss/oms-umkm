@extends('admin.layouts.app')

@section('title', 'Edit Produk — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Edit Produk',
    'description' => 'Perbarui detail menu pempek.',
    'actionRoute' => route('admin.produk.index'),
    'actionLabel' => 'Kembali',
])

<div class="max-w-3xl rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white p-6 sm:p-8">
    <form method="POST" action="{{ route('admin.produk.update', $produk) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')
        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Nama Produk</label>
                <input type="text" name="name" required value="{{ old('name', $produk->name) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Slug</label>
                <input type="text" name="slug" required value="{{ old('slug', $produk->slug) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
        </div>
        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Harga (Rp)</label>
                <input type="number" name="price" required min="0" value="{{ old('price', $produk->price) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Harga Coret (Rp, opsional)</label>
                <input type="number" name="price_strikethrough" min="0" value="{{ old('price_strikethrough', $produk->price_strikethrough) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
        </div>
        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Kategori</label>
                <select name="category_id" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
                    <option value="">Tanpa Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(old('category_id', $produk->category_id) == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Stok</label>
                <input type="number" name="stock" required min="0" value="{{ old('stock', $produk->stock) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
        </div>
        <div>
            <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Deskripsi Singkat</label>
            <textarea name="short_description" rows="2" required class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">{{ old('short_description', $produk->short_description) }}</textarea>
        </div>
        <div>
            <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Deskripsi Lengkap</label>
            <textarea name="description" rows="4" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">{{ old('description', $produk->description) }}</textarea>
        </div>
        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Komposisi</label>
                <input type="text" name="composition" value="{{ old('composition', $produk->composition) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Berat (gram)</label>
                <input type="number" name="weight" min="0" value="{{ old('weight', $produk->weight) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
        </div>
        <div>
            <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Gambar Utama</label>
            @if($produk->thumbnail)
                <div class="mb-2 flex items-center gap-3">
                    <img src="{{ asset($produk->thumbnail) }}" alt="" class="h-16 w-16 rounded-[var(--radius-md)] object-cover ring-1 ring-[var(--color-paper-3)]">
                    <label class="flex items-center gap-2 text-xs text-[var(--color-ink-2)]">
                        <input type="checkbox" name="remove_thumbnail" value="1"> Hapus gambar saat disimpan
                    </label>
                </div>
            @endif
            <input type="file" name="thumbnail" accept="image/jpeg,image/png,image/webp" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm file:mr-3 file:rounded-[var(--radius-md)] file:border-0 file:bg-[var(--color-accent-light)] file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-[var(--color-accent)] hover:file:bg-[var(--color-accent)] hover:file:text-white">
            <p class="mt-1 text-xs text-[var(--color-ink-3)]">{{ $produk->thumbnail ? 'Unggah file baru untuk mengganti gambar utama. ' : '' }}JPG/PNG/WebP, maksimal 2 MB.</p>
            @error('thumbnail')
                <p class="mt-1 text-xs text-[var(--color-danger)]">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Gambar Galeri</label>
            @if(!empty($produk->images))
                <div class="mb-3 grid grid-cols-3 gap-3 sm:grid-cols-4">
                    @foreach($produk->images as $i => $img)
                        <div class="relative">
                            <img src="{{ asset($img) }}" alt="" class="h-20 w-full rounded-[var(--radius-md)] object-cover ring-1 ring-[var(--color-paper-3)]">
                            <label class="absolute right-1 top-1 flex items-center gap-1 rounded bg-white/90 px-1.5 py-0.5 text-[10px] font-medium text-[var(--color-danger)] shadow-sm">
                                <input type="checkbox" name="remove_images[]" value="{{ $i }}" class="scale-75"> hapus
                            </label>
                        </div>
                    @endforeach
                </div>
                <p class="mb-3 text-xs text-[var(--color-ink-3)]">Centang gambar yang ingin dihapus, atau unggah file baru di bawah untuk menambah.</p>
            @endif
            <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm file:mr-3 file:rounded-[var(--radius-md)] file:border-0 file:bg-[var(--color-accent-light)] file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-[var(--color-accent)] hover:file:bg-[var(--color-accent)] hover:file:text-white">
            <p class="mt-1 text-xs text-[var(--color-ink-3)]">Tahan Ctrl/⌘ untuk memilih banyak file. JPG/PNG/WebP, masing-masing maks 2 MB.</p>
            @error('images.*')
                <p class="mt-1 text-xs text-[var(--color-danger)]">{{ $message }}</p>
            @enderror
        </div>
        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Status</label>
                <select name="status" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
                    <option value="active" @selected(old('status', $produk->status) === 'active')>Aktif</option>
                    <option value="inactive" @selected(old('status', $produk->status) === 'inactive')>Nonaktif</option>
                </select>
            </div>
            <div class="flex items-end gap-4 pb-2">
                <label class="flex items-center gap-2 text-sm text-[var(--color-ink-2)]">
                    <input type="checkbox" name="is_best_seller" value="1" @checked(old('is_best_seller', $produk->is_best_seller))> Best seller
                </label>
                <label class="flex items-center gap-2 text-sm text-[var(--color-ink-2)]">
                    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $produk->is_featured))> Unggulan
                </label>
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
