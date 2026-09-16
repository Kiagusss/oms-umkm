@extends('admin.layouts.app')

@section('title', 'Kelola Platform Toko UMKM — Multi-Tenant OMS')

@section('content')
<div class="space-y-6" x-data="{ openCreateModal: false }">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-800">Manajemen Toko & Mitra UMKM</h1>
            <p class="text-sm text-slate-500">Kelola multi-tenant toko kuliner, konfigurasi isolasi data, dan storefront publik.</p>
        </div>
        <div>
            <button
                type="button"
                @click="openCreateModal = true"
                class="inline-flex items-center gap-2 rounded-xl bg-emerald-700 px-4 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-emerald-800 transition"
            >
                + Tambah Toko UMKM Baru
            </button>
        </div>
    </div>

    {{-- Stores Table --}}
    <div class="rounded-2xl border border-slate-100 bg-white shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Daftar Seluruh Toko ({{ $stores->total() }})</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-4">Toko</th>
                        <th class="py-3.5 px-4">Slug / URL</th>
                        <th class="py-3.5 px-4">Kota</th>
                        <th class="py-3.5 px-4">Kontak</th>
                        <th class="py-3.5 px-4 text-center">Total Produk</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @foreach($stores as $st)
                    @php $isCurrent = ($currentStoreId == $st->id); @endphp
                    <tr class="hover:bg-slate-50/50 {{ $isCurrent ? 'bg-emerald-50/30' : '' }}">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-800 font-bold text-xs">
                                    {{ strtoupper(substr($st->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-slate-800">{{ $st->name }}</span>
                                        @if($isCurrent)
                                            <span class="rounded bg-emerald-700 text-white px-1.5 py-0.2 text-[9px] font-bold uppercase">Aktif</span>
                                        @endif
                                    </div>
                                    <span class="text-[11px] text-slate-400">{{ $st->email ?? 'No email' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-slate-500">
                            /store/{{ $st->slug }}
                        </td>
                        <td class="py-3.5 px-4">
                            {{ $st->city ?? 'Indonesia' }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-500">
                            {{ $st->phone ?? '-' }}
                        </td>
                        <td class="py-3.5 px-4 text-center font-bold text-slate-800">
                            {{ $st->products_count }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($st->is_active)
                                <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-[10px] font-bold text-emerald-700 border border-emerald-200">Aktif</span>
                            @else
                                <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[10px] font-bold text-slate-500">Nonaktif</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if(!$isCurrent)
                                <form method="POST" action="{{ route('admin.platform.switch') }}" class="m-0">
                                    @csrf
                                    <input type="hidden" name="store_id" value="{{ $st->id }}">
                                    <button type="submit" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-bold text-slate-700 hover:bg-slate-50 shadow-2xs">
                                        Pilih Toko
                                    </button>
                                </form>
                                @endif

                                <a href="{{ route('marketplace.storefront', $st->slug) }}" target="_blank" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-bold text-emerald-700 hover:bg-emerald-50 shadow-2xs">
                                    Lihat Storefront ↗
                                </a>

                                <form method="POST" action="{{ route('admin.platform.toggle', $st->id) }}" class="m-0">
                                    @csrf
                                    <button type="submit" class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-[11px] font-bold text-slate-500 hover:bg-slate-50" title="Ubah status">
                                        {{ $st->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $stores->links() }}
        </div>
    </div>

    {{-- Modal Tambah Toko --}}
    <div
        x-show="openCreateModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4"
        @keydown.escape.window="openCreateModal = false"
    >
        <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl border border-slate-100" @click.outside="openCreateModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-slate-800">Tambah Toko UMKM Baru</h3>
                <button type="button" @click="openCreateModal = false" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
            </div>

            <form method="POST" action="{{ route('admin.platform.store') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Toko *</label>
                    <input type="text" name="name" required placeholder="Contoh: Pempek Cek Mey 88" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-sm focus:border-emerald-600 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Slug (Opsional)</label>
                        <input type="text" name="slug" placeholder="pempek-cek-mey-88" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-sm focus:border-emerald-600 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kota</label>
                        <input type="text" name="city" placeholder="Palembang" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-sm focus:border-emerald-600 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email</label>
                        <input type="email" name="email" placeholder="toko@umkm.id" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-sm focus:border-emerald-600 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">No. WhatsApp / Telepon</label>
                        <input type="text" name="phone" placeholder="08123456789" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-sm focus:border-emerald-600 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Singkat</label>
                    <textarea name="description" rows="2" placeholder="Spesialis pempek panggang dan laksan khas Palembang..." class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-sm focus:border-emerald-600 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="openCreateModal = false" class="rounded-xl px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit" class="rounded-xl bg-emerald-700 px-5 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-emerald-800 transition">
                        Simpan Toko Baru
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
