@extends('admin.layouts.app')

@section('title', 'Edit Resep — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Edit Resep & Bill of Materials',
    'description' => 'Perbarui takaran bahan atau yield porsi.',
    'actionRoute' => route('admin.resep.index'),
    'actionLabel' => 'Kembali',
])

@php
    $initialItems = $recipe->items->map(function ($it) {
        $cost = (float) ($it->inventoryItem?->cost_per_unit ?? $it->inventoryItem?->unit_cost ?? $it->cost_per_unit ?? 0);
        return [
            'inventory_item_id' => (int) $it->inventory_item_id,
            'quantity' => (float) $it->quantity,
            'unit_cost' => $cost,
            'unit' => $it->inventoryItem?->unit ?? $it->unit ?? '',
        ];
    })->values()->all();

    if (empty($initialItems)) {
        $initialItems = [['inventory_item_id' => '', 'quantity' => 1, 'unit_cost' => 0, 'unit' => '']];
    }
@endphp

<div class="max-w-4xl rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white p-6 sm:p-8"
     x-data="{
         items: {{ json_encode($initialItems) }},
         inventoryOptions: {{ json_encode($inventoryItems) }},
         yieldVal: {{ (float) ($recipe->yield ?: 1) }},
         packagingCost: {{ (int) ($recipe->packaging_cost ?? 0) }},
         additionalCost: {{ (int) ($recipe->additional_cost ?? 0) }},
         addItem() {
             this.items.push({ inventory_item_id: '', quantity: 1, unit_cost: 0, unit: '' });
         },
         removeItem(idx) {
             if (this.items.length > 1) {
                 this.items.splice(idx, 1);
             }
         },
         onItemChange(idx) {
             let opt = this.inventoryOptions.find(i => Number(i.id) === Number(this.items[idx].inventory_item_id));
             let cost = opt ? (opt.cost_per_unit ?? opt.unit_cost ?? 0) : 0;
             this.items[idx].unit_cost = Number(cost) || 0;
             this.items[idx].unit = opt ? opt.unit : '';
         },
         totalMaterialCost() {
             return this.items.reduce((acc, row) => {
                 let q = parseFloat(row.quantity) || 0;
                 let c = parseFloat(row.unit_cost) || 0;
                 return acc + (q * c);
             }, 0);
         },
         totalBatchCost() {
             return this.totalMaterialCost() + (parseFloat(this.packagingCost) || 0) + (parseFloat(this.additionalCost) || 0);
         },
         hppPerPortion() {
             let y = parseFloat(this.yieldVal || 1);
             return y > 0 ? this.totalBatchCost() / y : 0;
         }
     }">
    <form method="POST" action="{{ route('admin.resep.update', $recipe) }}" class="space-y-6">
        @csrf
        @method('PUT')
        <div class="grid gap-6 sm:grid-cols-3">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Produk / Menu</label>
                <select name="product_id" required class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" @selected(old('product_id', $recipe->product_id) == $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Varian (Opsional)</label>
                <select name="variant_id" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
                    <option value="">Standar / Base</option>
                    @foreach($products as $p)
                        @foreach($p->variants as $v)
                            <option value="{{ $v->id }}" @selected(old('variant_id', $recipe->product_variant_id ?? $recipe->variant_id) == $v->id)>
                                {{ $p->name }} — {{ $v->name }}
                            </option>
                        @endforeach
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Hasil Resep (Porsi)</label>
                <input type="number" step="0.01" min="0.1" name="yield" x-model="yieldVal" required class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm font-bold">
            </div>
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Biaya Kemasan per Batch (Rp)</label>
                <input type="number" step="1" min="0" name="packaging_cost" x-model.number="packagingCost" placeholder="0" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm font-semibold">
                <p class="mt-1 text-xs text-slate-400">Contoh: kotak mika, plastik vacuum, kantong kresek</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Biaya Tenaga & Gas per Batch (Rp)</label>
                <input type="number" step="1" min="0" name="additional_cost" x-model.number="additionalCost" placeholder="0" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm font-semibold">
                <p class="mt-1 text-xs text-slate-400">Contoh: gas LPG, tenaga masak, listrik penggorengan</p>
            </div>
        </div>

        {{-- Dynamic Ingredient Rows --}}
        <div>
            <div class="flex items-center justify-between mb-2">
                <label class="text-sm font-semibold text-[var(--color-ink)]">Daftar Bahan Baku & Kemasan</label>
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
                                <option value="">Pilih Bahan...</option>
                                <template x-for="opt in inventoryOptions" :key="opt.id">
                                    <option :value="opt.id" :selected="opt.id == row.inventory_item_id" x-text="opt.name + ' (' + opt.unit + ') - Rp ' + Number(opt.cost_per_unit || opt.unit_cost || 0).toLocaleString('id-ID')"></option>
                                </template>
                            </select>
                        </div>
                        <div class="w-32">
                            <input type="number" step="0.001" min="0.0001" :name="'items['+idx+'][quantity]'" x-model="row.quantity" placeholder="Takaran" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                        </div>
                        <div class="w-20 text-xs font-semibold text-slate-500" x-text="row.unit"></div>
                        <div class="w-36 text-right font-mono text-xs font-semibold text-slate-700">
                            Rp <span x-text="Number((row.quantity || 0) * (row.unit_cost || 0)).toLocaleString('id-ID')"></span>
                        </div>
                        <button type="button" @click="removeItem(idx)" class="text-rose-500 hover:text-rose-700 p-1" title="Hapus bahan">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>
                </template>
            </div>
        </div>

        {{-- Live Cost Preview --}}
        <div class="rounded-2xl border border-emerald-100 bg-emerald-50/50 p-4 grid gap-4 sm:grid-cols-3">
            <div>
                <p class="text-xs font-semibold text-emerald-800">Biaya Bahan Baku:</p>
                <p class="text-base font-bold text-emerald-900 font-mono">Rp <span x-text="Number(totalMaterialCost().toFixed(0)).toLocaleString('id-ID')"></span></p>
            </div>
            <div>
                <p class="text-xs font-semibold text-emerald-800">Total Biaya Batch (+ Kemasan/Gas):</p>
                <p class="text-base font-bold text-emerald-900 font-mono">Rp <span x-text="Number(totalBatchCost().toFixed(0)).toLocaleString('id-ID')"></span></p>
            </div>
            <div class="sm:text-right">
                <p class="text-xs font-semibold text-emerald-800">Estimasi HPP Bersih per Porsi:</p>
                <p class="text-2xl font-black text-emerald-700 font-mono">Rp <span x-text="Number(hppPerPortion().toFixed(0)).toLocaleString('id-ID')"></span></p>
            </div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Petunjuk Pembuatan / Instruksi SOP</label>
            <textarea name="instructions" rows="3" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">{{ old('instructions', $recipe->notes ?? $recipe->instructions) }}</textarea>
        </div>

        <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
            <a href="{{ route('admin.resep.index') }}" class="rounded-[var(--radius-md)] border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</a>
            <button type="submit" class="rounded-[var(--radius-md)] bg-[var(--color-accent)] px-5 py-2 text-sm font-semibold text-white hover:bg-[var(--color-accent-hover)]">Simpan & Perbarui HPP</button>
        </div>
    </form>
</div>
@endsection
