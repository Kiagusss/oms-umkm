@extends('admin.layouts.app')

@section('title', 'Detail Pesanan #{{ $pesanan->id }} — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Detail Pesanan #' . $pesanan->id,
    'description' => 'Lihat rincian lengkap pesanan pelanggan.',
    'actionRoute' => route('admin.pesanan.index'),
    'actionLabel' => 'Kembali',
])

<div class="mb-4 flex justify-end">
    <a href="{{ route('admin.pesanan.struk', $pesanan) }}"
       class="inline-flex items-center gap-2 rounded-[var(--radius-md)] bg-[var(--color-accent)] px-4 py-2 text-sm font-semibold text-white hover:opacity-90">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        Cetak Struk PDF
    </a>
</div>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white p-6 lg:col-span-2">
        <h2 class="mb-4 text-base font-semibold text-[var(--color-ink)]">Item Pesanan</h2>
        @php $items = json_decode($pesanan->products ?? '[]', true); @endphp
        @if(count($items))
            <ul class="divide-y divide-[var(--color-paper-3)]">
                @foreach($items as $item)
                    <li class="flex items-center gap-3 py-3">
                        @if(!empty($item['thumbnail']))
                            <img src="{{ asset($item['thumbnail']) }}" class="h-11 w-11 rounded-[var(--radius-md)] object-cover" alt="">
                        @endif
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-[var(--color-ink)]">{{ $item['productName'] ?? $item['name'] ?? '-' }}</p>
                            <p class="text-xs text-[var(--color-ink-3)]">{{ $item['quantity'] }} × Rp{{ number_format($item['price'] ?? 0) }}</p>
                        </div>
                        <span class="text-sm font-bold tabular-nums text-[var(--color-ink)]">Rp{{ number_format(($item['price'] ?? 0) * $item['quantity']) }}</span>
                    </li>
                @endforeach
            </ul>
            <div class="mt-4 flex items-center justify-between border-t border-[var(--color-paper-3)] pt-4">
                <span class="text-sm font-medium text-[var(--color-ink-2)]">Total</span>
                <span class="text-xl font-bold tabular-nums text-[var(--color-ink)]">
                    Rp{{ number_format(collect($items)->sum(fn ($i) => ($i['price'] ?? 0) * $i['quantity'])) }}
                </span>
            </div>
        @else
            <p class="py-6 text-center text-sm text-[var(--color-ink-3)]">Tidak ada item.</p>
        @endif

        @if($pesanan->notes)
            <div class="mt-6 rounded-[var(--radius-lg)] bg-[var(--color-paper-2)] p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-[var(--color-ink-3)]">Catatan</p>
                <p class="mt-1 text-sm text-[var(--color-ink)]">{{ $pesanan->notes }}</p>
            </div>
        @endif
    </div>

    <div class="space-y-4">
        <div class="rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white p-6">
            <h2 class="mb-4 text-base font-semibold text-[var(--color-ink)]">Informasi Pelanggan</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-[var(--color-ink-3)]">Nama</dt><dd class="font-medium text-[var(--color-ink)]">{{ $pesanan->name }}</dd></div>
                <div class="flex justify-between"><dt class="text-[var(--color-ink-3)]">WhatsApp</dt><dd class="font-medium text-[var(--color-ink)]">{{ $pesanan->whatsapp }}</dd></div>
                <div class="flex justify-between"><dt class="text-[var(--color-ink-3)]">Tanggal</dt><dd class="font-medium text-[var(--color-ink)]">{{ \Carbon\Carbon::parse($pesanan->date)->translatedFormat('d M Y') }}</dd></div>
            </dl>
        </div>

        <div class="rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white p-6">
            <h2 class="mb-4 text-base font-semibold text-[var(--color-ink)]">Ubah Status</h2>
            <form method="POST" action="{{ route('admin.pesanan.update', $pesanan) }}" class="flex gap-2">
                @csrf @method('PUT')
                <select name="status" class="flex-1 rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
                    @foreach(['pending' => 'Pending', 'processing' => 'Diproses', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'] as $v => $l)
                        <option value="{{ $v }}" @selected($pesanan->status === $v)>{{ $l }}</option>
                    @endforeach
                </select>
                <button class="rounded-[var(--radius-md)] bg-[var(--color-accent)] px-4 py-2 text-sm font-semibold text-white">Simpan</button>
            </form>
        </div>
    </div>
</div>
@endsection
