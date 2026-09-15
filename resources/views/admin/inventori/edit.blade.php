@extends('admin.layouts.app')

@section('title', 'Edit Bahan — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Edit Bahan Inventori',
    'description' => 'Perbarui spesifikasi bahan atau batas minimum stok.',
    'actionRoute' => route('admin.inventori.index'),
    'actionLabel' => 'Kembali',
])

<div class="max-w-3xl rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white p-6 sm:p-8">
    <form method="POST" action="{{ route('admin.inventori.update', $item) }}" class="space-y-6">
        @csrf
        @method('PUT')
        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Kode SKU Bahan</label>
                <input type="text" name="sku" required value="{{ old('sku', $item->sku) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm uppercase">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Nama Bahan Baku / Packaging</label>
                <input type="text" name="name" required value="{{ old('name', $item->name) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
        </div>

        <div class="grid gap-6 sm:grid-cols-3">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Kategori Bahan</label>
                <select name="category" required class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
                    <option value="raw_material" @selected(old('category', $item->category) === 'raw_material')>Bahan Baku (Raw)</option>
                    <option value="packaging" @selected(old('category', $item->category) === 'packaging')>Kemasan (Packaging)</option>
                    <option value="intermediate" @selected(old('category', $item->category) === 'intermediate')>Bahan Setengah Jadi</option>
                    <option value="finished_good" @selected(old('category', $item->category) === 'finished_good')>Barang Jadi (Finished)</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Satuan (Unit)</label>
                <input type="text" name="unit" required value="{{ old('unit', $item->unit) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Biaya Satuan Standar (Rp)</label>
                <input type="number" step="0.01" name="unit_cost" required value="{{ old('unit_cost', $item->unit_cost) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Batas Minimum Stok (Peringatan)</label>
                <input type="number" step="0.01" name="min_stock" required value="{{ old('min_stock', $item->min_stock) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Deskripsi / Catatan Tambahan</label>
            <textarea name="description" rows="3" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">{{ old('description', $item->description) }}</textarea>
        </div>

        <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
            <a href="{{ route('admin.inventori.index') }}" class="rounded-[var(--radius-md)] border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</a>
            <button type="submit" class="rounded-[var(--radius-md)] bg-[var(--color-accent)] px-5 py-2 text-sm font-semibold text-white hover:bg-[var(--color-accent-hover)]">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection
