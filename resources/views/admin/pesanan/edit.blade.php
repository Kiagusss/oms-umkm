@extends('admin.layouts.app')

@section('title', 'Edit Pesanan #{{ $pesanan->id }} — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Edit Pesanan #' . $pesanan->id,
    'description' => 'Perbarui data pesanan pelanggan.',
    'actionRoute' => route('admin.pesanan.index'),
    'actionLabel' => 'Kembali',
])

<div class="max-w-3xl rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white p-6 sm:p-8">
    <form method="POST" action="{{ route('admin.pesanan.update', $pesanan) }}" class="space-y-6">
        @csrf @method('PUT')
        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Nama Pelanggan</label>
                <input type="text" name="name" required value="{{ old('name', $pesanan->name) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Nomor WhatsApp</label>
                <input type="text" name="whatsapp" required value="{{ old('whatsapp', $pesanan->whatsapp) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
        </div>
        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Tanggal</label>
                <input type="date" name="date" required value="{{ old('date', $pesanan->date) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Status</label>
                <select name="status" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
                    @foreach(['pending' => 'Pending', 'processing' => 'Diproses', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'] as $v => $l)
                        <option value="{{ $v }}" @selected(old('status', $pesanan->status) === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div>
            <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Catatan</label>
            <textarea name="notes" rows="3" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">{{ old('notes', $pesanan->notes) }}</textarea>
        </div>

        <div class="flex justify-end border-t border-[var(--color-paper-3)] pt-6">
            <button type="submit" class="rounded-[var(--radius-xl)] bg-[var(--color-accent)] px-6 py-2.5 text-sm font-semibold text-white transition-all hover:bg-[var(--color-accent-hover)]">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
