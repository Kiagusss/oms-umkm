@extends('admin.layouts.app')

@section('title', 'Buat Purchase Order — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Buat Purchase Order (PO)',
    'description' => 'Input faktur pembelian bahan baku dari supplier ke gudang cabang.',
    'actionRoute' => route('admin.pembelian.index'),
    'actionLabel' => 'Kembali',
])

<div class="max-w-4xl rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white p-6 sm:p-8"
     x-data="{
         items: [
             { inventory_item_id: '', quantity: 1, unit_cost: 0, unit: '' }
         ],
         inventoryOptions: {{ json_encode($inventoryItems) }},
         addItem() {
             this.items.push({ inventory_item_id: '', quantity: 1, unit_cost: 0, unit: '' });
         },
         removeItem(idx) {
             if (this.items.length > 1) {
                 this.items.splice(idx, 1);
             }
         },
         onItemChange(idx) {
             let opt = this.inventoryOptions.find(i => i.id == this.items[idx].inventory_item_id);
             this.items[idx].unit_cost = opt ? opt.unit_cost : 0;
             this.items[idx].unit = opt ? opt.unit : '';
         },
         totalPO() {
             return this.items.reduce((acc, row) => acc + (parseFloat(row.quantity || 0) * parseFloat(row.unit_cost || 0)), 0);
         }
     }">
    <form method="POST" action="{{ route('admin.pembelian.store') }}" class="space-y-6">
        @csrf
        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">No. Faktur / PO</label>
                <input type="text" name="invoice_number" value="PO-{{ date('Ymd') }}-{{ rand(100,999) }}" required class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm font-mono font-bold">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Cabang Penerima</label>
                <select name="branch_id" required class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm font-semibold">
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Supplier</label>
                <select name="supplier_id" required class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm font-semibold">
                    <option value="">Pilih Supplier...</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Tanggal Pembelian</label>
                <input type="date" name="purchase_date" value="{{ date('Y-m-d') }}" required class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Catatan PO</label>
                <input type="text" name="notes" placeholder="Contoh: Pengiriman via armada supplier langsung" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
        </div>

        {{-- Dynamic Items --}}
        <div>
            <div class="flex items-center justify-between mb-2">
                <label class="text-sm font-semibold text-[var(--color-ink)]">Daftar Bahan Yang Dipesan</label>
                <button type="button" @click="addItem()" class="inline-flex items-center gap-1 text-xs font-bold text-emerald-700 hover:text-emerald-800">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Tambah Bahan
                </button>
            </div>

            <div class="space-y-3">
                <template x-for="(row, idx) in items" :key="idx">
                    <div class="flex flex-wrap sm:flex-nowrap items-center gap-3 rounded-xl border border-slate-200 bg-slate-50/50 p-3">
                        <div class="flex-1 min-w-[200px]">
                            <select :name="'items['+idx+'][inventory_item_id]'" x-model="row.inventory_item_id" @change="onItemChange(idx)" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                                <option value="">Pilih Bahan Baku...</option>
                                <template x-for="opt in inventoryOptions" :key="opt.id">
                                    <option :value="opt.id" x-text="opt.name + ' (' + opt.unit + ')'"></option>
                                </template>
                            </select>
                        </div>
                        <div class="w-28">
                            <input type="number" step="0.01" min="0.01" :name="'items['+idx+'][quantity]'" x-model="row.quantity" placeholder="Qty" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                        </div>
                        <div class="w-16 text-xs font-semibold text-slate-500" x-text="row.unit"></div>
                        <div class="w-36">
                            <input type="number" step="1" min="0" :name="'items['+idx+'][unit_cost]'" x-model="row.unit_cost" placeholder="Harga Satuan" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-mono">
                        </div>
                        <div class="w-36 text-right font-mono text-xs font-bold text-slate-800">
                            Rp <span x-text="Number((row.quantity || 0) * (row.unit_cost || 0)).toLocaleString('id-ID')"></span>
                        </div>
                        <button type="button" @click="removeItem(idx)" class="text-rose-500 hover:text-rose-700 p-1">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 flex justify-between items-center">
            <span class="text-sm font-bold text-slate-700">Total Nilai Purchase Order:</span>
            <span class="text-2xl font-black text-slate-900 font-mono">Rp <span x-text="Number(totalPO()).toLocaleString('id-ID')"></span></span>
        </div>

        <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
            <a href="{{ route('admin.pembelian.index') }}" class="rounded-[var(--radius-md)] border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</a>
            <button type="submit" class="rounded-[var(--radius-md)] bg-[var(--color-accent)] px-5 py-2 text-sm font-semibold text-white hover:bg-[var(--color-accent-hover)]">Simpan Order Pembelian</button>
        </div>
    </form>
</div>
@endsection
