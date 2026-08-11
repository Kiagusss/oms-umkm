@extends('admin.layouts.app')

@section('title', 'Edit Voucher — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Edit Voucher',
    'description' => 'Perbarui data voucher.',
    'actionRoute' => route('admin.voucher.index'),
    'actionLabel' => 'Kembali',
])

<div class="max-w-3xl rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white p-6 sm:p-8">
    <form method="POST" action="{{ route('admin.voucher.update', $voucher) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Kode Voucher *</label>
                <input type="text" name="code" required value="{{ old('code', $voucher->code) }}" placeholder="HEM10" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm uppercase">
                @error('code')<p class="mt-1 text-xs text-[var(--color-danger)]">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Tipe Diskon *</label>
                <select name="type" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
                    <option value="percentage" @selected(old('type', $voucher->type) === 'percentage')>Persentase (%)</option>
                    <option value="fixed" @selected(old('type', $voucher->type) === 'fixed')>Nominal (Rp)</option>
                </select>
                @error('type')<p class="mt-1 text-xs text-[var(--color-danger)]">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Nilai Diskon *</label>
                <input type="number" name="value" required step="0.01" min="0.01" value="{{ old('value', $voucher->value) }}" placeholder="10 atau 5000" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
                @error('value')<p class="mt-1 text-xs text-[var(--color-danger)]">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Min. Order (opsional)</label>
                <input type="number" name="min_order" step="0.01" min="0" value="{{ old('min_order', $voucher->min_order ?? '') }}" placeholder="Kosong = tanpa minimal" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
                @error('min_order')<p class="mt-1 text-xs text-[var(--color-danger)]">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Maks. Penggunaan (opsional)</label>
                <input type="number" name="max_uses" min="1" value="{{ old('max_uses', $voucher->max_uses ?? '') }}" placeholder="Kosong = tanpa batas" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
                @error('max_uses')<p class="mt-1 text-xs text-[var(--color-danger)]">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2"></div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Berlaku Mulai (opsional)</label>
                <input type="datetime-local" name="valid_from" value="{{ old('valid_from', $voucher->valid_from?->format('Y-m-d\TH:i')) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
                @error('valid_from')<p class="mt-1 text-xs text-[var(--color-danger)]">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Berlaku Sampai (opsional)</label>
                <input type="datetime-local" name="valid_until" value="{{ old('valid_until', $voucher->valid_until?->format('Y-m-d\TH:i')) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
                @error('valid_until')<p class="mt-1 text-xs text-[var(--color-danger)]">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label class="flex cursor-pointer items-center gap-3">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $voucher->is_active)) class="h-4 w-4 rounded border-[var(--color-paper-3)] text-[var(--color-accent)]">
                    <span class="text-sm font-semibold text-[var(--color-ink)]">Aktif</span>
                    <span class="text-xs text-[var(--color-ink-3)]">Voucher nonaktif tidak bisa dipakai di kasir.</span>
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
