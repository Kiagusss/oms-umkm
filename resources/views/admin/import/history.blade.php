@extends('admin.layouts.app')

@section('title', 'Riwayat Import Katalog — Multi-Tenant OMS')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-800">Riwayat Import Katalog</h1>
            <p class="text-sm text-slate-500">Log dan audit trail seluruh operasi migrasi dan sinkronisasi produk UMKM.</p>
        </div>
        <div>
            <a href="{{ route('admin.import.index') }}" class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold text-white shadow-xs hover:opacity-95 transition bg-[var(--color-accent,#0f4a2d)]" style="background-color: #0f4a2d !important; color: #ffffff !important;">
                + Import Katalog Baru
            </a>
        </div>
    </div>

    {{-- History Table Card --}}
    <div class="rounded-2xl border border-slate-100 bg-white shadow-xs overflow-hidden">
        @if($imports->isEmpty())
        <div class="p-12 text-center">
            <svg class="mx-auto text-slate-300 mb-3" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="text-base font-bold text-slate-800">Belum Ada Riwayat Import</h3>
            <p class="text-xs text-slate-500 mt-1">Gunakan tombol "Import Katalog Baru" untuk mengimpor produk pertama.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-4">ID</th>
                        <th class="py-3.5 px-4">Toko Tujuan</th>
                        <th class="py-3.5 px-4">Sumber</th>
                        <th class="py-3.5 px-4">Strategi</th>
                        <th class="py-3.5 px-4 text-center">Total</th>
                        <th class="py-3.5 px-4 text-center">Sukses</th>
                        <th class="py-3.5 px-4 text-center">Lewati</th>
                        <th class="py-3.5 px-4 text-center">Gagal</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4">Waktu</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @foreach($imports as $imp)
                    <tr class="hover:bg-slate-50/50">
                        <td class="py-3 px-4 font-mono font-bold text-slate-800">
                            #{{ $imp->id }}
                        </td>
                        <td class="py-3 px-4 font-semibold text-slate-800">
                            {{ $imp->store->name ?? '-' }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="rounded bg-slate-100 px-2 py-0.5 font-mono text-[10px] uppercase font-bold text-slate-700">
                                {{ $imp->source_type }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-slate-500 capitalize">
                            {{ $imp->duplicate_strategy }}
                        </td>
                        <td class="py-3 px-4 text-center font-bold text-slate-800">
                            {{ $imp->total_items }}
                        </td>
                        <td class="py-3 px-4 text-center font-bold text-emerald-600">
                            {{ $imp->imported_items }}
                        </td>
                        <td class="py-3 px-4 text-center font-bold text-amber-600">
                            {{ $imp->skipped_items }}
                        </td>
                        <td class="py-3 px-4 text-center font-bold text-rose-600">
                            {{ $imp->failed_items }}
                        </td>
                        <td class="py-3 px-4">
                            @if($imp->status === 'completed')
                                <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-[10px] font-bold text-emerald-700 border border-emerald-200">Selesai</span>
                            @elseif($imp->status === 'processing')
                                <span class="rounded-full bg-blue-50 px-2.5 py-0.5 text-[10px] font-bold text-blue-700 border border-blue-200">Memproses</span>
                            @elseif($imp->status === 'failed')
                                <span class="rounded-full bg-rose-50 px-2.5 py-0.5 text-[10px] font-bold text-rose-700 border border-rose-200">Gagal</span>
                            @else
                                <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[10px] font-bold text-slate-600">{{ $imp->status }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-400">
                            {{ $imp->created_at->format('d M Y, H:i') }}
                        </td>
                        <td class="py-3 px-4 text-right">
                            <a href="{{ route('admin.import.show', $imp->id) }}" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-bold text-slate-700 hover:bg-slate-50">
                                Detail →
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $imports->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
