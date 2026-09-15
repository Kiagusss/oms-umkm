@extends('admin.layouts.app')

@section('title', 'Tambah Bahan — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Tambah Bahan Inventori',
    'description' => 'Daftarkan bahan baku atau kemasan baru.',
    'actionRoute' => route('admin.inventori.index'),
    'actionLabel' => 'Kembali',
])

<div class="max-w-3xl rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white p-6 sm:p-8">
    <form method="POST" action="{{ route('admin.inventori.store') }}" class="space-y-6">
        @csrf
        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Kode SKU Bahan</label>
                <input type="text" name="sku" required value="{{ old('sku') }}" placeholder="Misal: RAW-IKAN-TENGGIRI" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm uppercase">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Nama Bahan Baku / Packaging</label>
                <input type="text" name="name" required value="{{ old('name') }}" placeholder="Misal: Daging Ikan Tenggiri Giling" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
        </div>

        <div class="grid gap-6 sm:grid-cols-3">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Kategori Bahan</label>
                <select name="category" required class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
                    <option value="raw_material" @selected(old('category') === 'raw_material')>Bahan Baku (Raw)</option>
                    <option value="packaging" @selected(old('category') === 'packaging')>Kemasan (Packaging)</option>
                    <option value="intermediate" @selected(old('category') === 'intermediate')>Bahan Setengah Jadi</option>
                    <option value="finished_good" @selected(old('category') === 'finished_good')>Barang Jadi (Finished)</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Satuan (Unit)</label>
                <input type="text" name="unit" required value="{{ old('unit', 'gram') }}" placeholder="gram, kg, ml, pcs" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Biaya Standar Satuan (Rp)</label>
                <input type="number" step="0.01" name="unit_cost" required value="{{ old('unit_cost', 0) }}" placeholder="Harga per unit satuan" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Batas Minimum Stok (Peringatan)</label>
                <input type="number" step="0.01" name="min_stock" required value="{{ old('min_stock', 10) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Stok Awal di Cabang (Opsional)</label>
                <div class="flex gap-2">
                    <input type="number" step="0.01" name="initial_stock" value="{{ old('initial_stock', 0) }}" placeholder="Jumlah stok awal" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
                    <select name="branch_id" class="rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-xs">
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Deskripsi / Catatan Tambahan</label>
            <textarea name="description" rows="3" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">{{ old('description') }}</textarea>
        </div>

        <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
            <a href="{{ route('admin.inventori.index') }}" class="rounded-[var(--radius-md)] border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</a>
            <button type="submit" class="rounded-[var(--radius-md)] bg-[var(--color-accent)] px-5 py-2 text-sm font-semibold text-white hover:bg-[var(--color-accent-hover)]">Simpan Bahan</button>
        </div>
    </form>
</div>
@endsection
