@extends('admin.layouts.app')

@section('title', 'Edit Supplier — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Edit Supplier: ' . $supplier->name,
    'description' => 'Perbarui data kontak atau status supplier.',
    'actionRoute' => route('admin.supplier.index'),
    'actionLabel' => 'Kembali',
])

<div class="max-w-2xl rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white p-6 sm:p-8">
    <form method="POST" action="{{ route('admin.supplier.update', $supplier) }}" class="space-y-5">
        @csrf
        @method('PUT')
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Kode Supplier</label>
                <input type="text" name="code" value="{{ old('code', $supplier->code) }}" required class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm font-mono uppercase">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Nama Perusahaan / Toko</label>
                <input type="text" name="name" value="{{ old('name', $supplier->name) }}" required class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Contact Person (PIC)</label>
                <input type="text" name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Nomor Telepon / WhatsApp</label>
                <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Email</label>
                <input type="email" name="email" value="{{ old('email', $supplier->email) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Status</label>
                <select name="is_active" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
                    <option value="1" @selected(old('is_active', $supplier->is_active) == 1)>Aktif</option>
                    <option value="0" @selected(old('is_active', $supplier->is_active) == 0)>Nonaktif</option>
                </select>
            </div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Alamat Lengkap</label>
            <textarea name="address" rows="2" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">{{ old('address', $supplier->address) }}</textarea>
        </div>

        <div class="pt-3 flex justify-end gap-3">
            <a href="{{ route('admin.supplier.index') }}" class="rounded-[var(--radius-md)] border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</a>
            <button type="submit" class="rounded-[var(--radius-md)] bg-[var(--color-accent)] px-5 py-2 text-sm font-semibold text-white hover:bg-[var(--color-accent-hover)]">Perbarui Supplier</button>
        </div>
    </form>
</div>
@endsection
