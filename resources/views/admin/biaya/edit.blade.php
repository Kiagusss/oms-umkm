@extends('admin.layouts.app')

@section('title', 'Edit Biaya — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Edit Biaya Operasional',
    'description' => 'Perbarui data pencatatan pengeluaran kas operasional.',
    'actionRoute' => route('admin.biaya.index'),
    'actionLabel' => 'Kembali',
])

<div class="max-w-2xl rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white p-6 sm:p-8">
    <form method="POST" action="{{ route('admin.biaya.update', $expense) }}" class="space-y-5">
        @csrf
        @method('PUT')
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Cabang</label>
                <select name="branch_id" required class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm font-semibold">
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" @selected(old('branch_id', $expense->branch_id) == $b->id)>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Kategori Beban</label>
                <select name="category" required class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm font-semibold capitalize">
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" @selected(old('category', $expense->category) == $cat)>{{ str_replace('_', ' ', $cat) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Nominal Biaya (Rp)</label>
                <input type="number" step="100" min="1" name="amount" value="{{ old('amount', (int)$expense->amount) }}" required class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm font-mono font-bold">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Tanggal Transaksi</label>
                <input type="date" name="expense_date" value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" required class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Deskripsi / Keterangan</label>
            <input type="text" name="description" value="{{ old('description', $expense->description) }}" required class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Metode Pembayaran</label>
                <select name="payment_method" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
                    <option value="cash" @selected(old('payment_method', $expense->payment_method) == 'cash')>Cash / Tunai Kasir</option>
                    <option value="bank_transfer" @selected(old('payment_method', $expense->payment_method) == 'bank_transfer')>Transfer Bank</option>
                    <option value="qris" @selected(old('payment_method', $expense->payment_method) == 'qris')>QRIS / E-Wallet</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">No. Referensi / Kwitansi (Opsional)</label>
                <input type="text" name="reference_number" value="{{ old('reference_number', $expense->reference_number) }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm font-mono">
            </div>
        </div>

        <div class="pt-3 flex justify-end gap-3">
            <a href="{{ route('admin.biaya.index') }}" class="rounded-[var(--radius-md)] border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</a>
            <button type="submit" class="rounded-[var(--radius-md)] bg-[var(--color-accent)] px-5 py-2 text-sm font-semibold text-white hover:bg-[var(--color-accent-hover)]">Perbarui Biaya</button>
        </div>
    </form>
</div>
@endsection
