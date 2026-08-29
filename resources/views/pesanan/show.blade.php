@extends('layouts.app')

@section('title', 'Pesanan #' . $order->id . ' — ' . config('app.name', 'Pempek Palembang'))

@section('content')
<div class="mx-auto max-w-2xl px-4 py-14 sm:px-6 lg:px-8">
    @if($justCreated)
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-center">
            <p class="text-sm font-semibold text-emerald-700">Pesanan #{{ $order->id }} berhasil dibuat!</p>
            <p class="mt-1 text-xs text-emerald-600">Silakan selesaikan pembayaran QRIS di bawah.</p>
        </div>
    @elseif($justPaid)
        <div class="mb-6 rounded-2xl border border-sky-200 bg-sky-50 px-5 py-4 text-center">
            <p class="text-sm font-semibold text-sky-700">Pembayaran QRIS berhasil dikonfirmasi!</p>
            <p class="mt-1 text-xs text-sky-600">Pesanan kamu sedang diproses.</p>
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        {{-- Header --}}
        <div class="border-b border-slate-100 px-6 py-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-lg font-bold text-slate-800">Pesanan #{{ $order->id }}</h1>
                    <p class="mt-0.5 text-xs text-slate-400">{{ $order->created_at->translatedFormat('d F Y, H:i') }}</p>
                </div>
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold"
                    @php
                        $statusClasses = [
                            'pending' => 'bg-amber-100 text-amber-700',
                            'processing' => 'bg-sky-100 text-sky-700',
                            'completed' => 'bg-emerald-100 text-emerald-700',
                            'cancelled' => 'bg-red-100 text-red-700',
                        ];
                        $statusLabels = [
                            'pending' => 'Menunggu Pembayaran',
                            'processing' => 'Sedang Dibuat',
                            'completed' => 'Selesai',
                            'cancelled' => 'Dibatalkan',
                        ];
                    @endphp
                    class="{{ $statusClasses[$order->status] ?? 'bg-slate-100 text-slate-600' }}">
                    {{ $statusLabels[$order->status] ?? $order->status }}
                </span>
            </div>
        </div>

        {{-- Items --}}
        <div class="px-6 py-4">
            <h2 class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Pesanan</h2>
            <ul class="space-y-3">
                @php $items = is_array($order->products) ? $order->products : json_decode((string) $order->products, true) ?? []; @endphp
                @foreach($items as $item)
                    <li class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            @if(!empty($item['thumbnail']))
                                <img src="{{ asset($item['thumbnail']) }}" alt="{{ $item['productName'] }}" class="h-10 w-10 rounded-lg object-cover">
                            @else
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-xs text-slate-400">?</div>
                            @endif
                            <div>
                                <p class="text-sm font-semibold text-slate-700">{{ $item['productName'] }}</p>
                                <p class="text-xs text-slate-400">{{ $item['quantity'] }} x Rp{{ number_format($item['price'], 0, ',', '.') }}</p>
                            </div>
                        </div>
                        <p class="text-sm font-semibold text-slate-700">Rp{{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</p>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Total --}}
        <div class="border-t border-slate-100 px-6 py-4">
            @if($order->discount > 0)
                <div class="flex justify-between text-sm text-slate-500">
                    <span>Subtotal</span>
                    <span>Rp{{ number_format($order->total(), 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm text-emerald-600">
                    <span>Diskon</span>
                    <span>-Rp{{ number_format($order->discount, 0, ',', '.') }}</span>
                </div>
            @endif
            @if($order->shipping_cost > 0)
                <div class="flex justify-between text-sm text-slate-500">
                    <span>Ongkir ({{ $order->shipping_courier }})</span>
                    <span>Rp{{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                </div>
            @endif
            <div class="mt-1 flex justify-between text-base font-bold text-slate-800">
                <span>Total Dibayar</span>
                <span>Rp{{ number_format($order->grandTotal(), 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- QRIS pending — tampilkan QR + konfirmasi --}}
        @if($order->payment_method === 'QRIS' && $order->status === 'pending')
            <div class="border-t border-slate-100 px-6 py-5 text-center">
                <h3 class="mb-1 text-sm font-bold text-slate-700">Pembayaran QRIS</h3>
                <p class="mb-4 text-xs text-slate-400">Scan QR di bawah untuk simulasi pembayaran</p>
                <div class="mb-4 flex justify-center">
                    <canvas id="qris-customer-canvas" width="220" height="220"></canvas>
                </div>
                <p class="mb-4 text-lg font-bold text-emerald-600">Rp{{ number_format($order->grandTotal(), 0, ',', '.') }}</p>
                <button id="btn-qris-paid"
                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-6 py-3 text-sm font-bold text-white transition hover:bg-emerald-500 active:scale-[0.98]">
                    Saya Sudah Bayar
                </button>
                <p class="mt-2 text-xs text-slate-400">Setelah klik, pesanan masuk ke proses pembuatan.</p>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/qrious@4.0.2/dist/qrious.min.js"></script>
            <script>
                (function(){
                    var el = document.getElementById('qris-customer-canvas');
                    if (el && window.QRious) {
                        new QRious({ element: el, value: '{{ $qrisPayload }}', size: 220, level: 'M' });
                    }
                    document.getElementById('btn-qris-paid').addEventListener('click', async function(){
                        var btn = this;
                        btn.disabled = true;
                        btn.textContent = 'Memproses…';
                        try {
                            var res = await fetch('/api/public/qris/settle', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                                body: JSON.stringify({ order_id: {{ $order->id }}, token: '{{ $qrisToken }}' })
                            });
                            var data = await res.json();
                            if (res.ok) {
                                window.location.reload();
                            } else {
                                alert('Gagal: ' + (data.error || 'unknown'));
                                btn.disabled = false;
                                btn.textContent = 'Saya Sudah Bayar';
                            }
                        } catch(e) {
                            alert('Koneksi error: ' + e.message);
                            btn.disabled = false;
                            btn.textContent = 'Saya Sudah Bayar';
                        }
                    });
                })();
            </script>
        @endif

        {{-- Konfirmasi WhatsApp ke admin setelah bayar --}}
        @if($order->payment_method === 'QRIS' && $order->status === 'processing')
            <div class="border-t border-slate-100 px-6 py-5 text-center">
                @php
                    $waNum = config('app.wa_number', '');
                    $waText = "Halo admin, saya {$order->name} sudah membayar QRIS untuk Pesanan #{$order->id} (Rp" . number_format($order->grandTotal(), 0, ',', '.') . "). Mohon dikonfirmasi ya 🙏";
                @endphp
                <p class="mb-3 text-sm text-slate-600">Terakhir: kabari admin supaya pesanan segera diproses.</p>
                <a href="https://wa.me/{{ $waNum }}?text={{ urlencode($waText) }}" target="_blank" rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 rounded-xl bg-[#25D366] px-6 py-3 text-sm font-bold text-white transition hover:opacity-90 active:scale-[0.98]">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                    Konfirmasi via WhatsApp
                </a>
            </div>
        @endif

        {{-- Info pelanggan --}}
        <div class="border-t border-slate-100 px-6 py-4">
            <h2 class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Info Pelanggan</h2>
            <p class="text-sm text-slate-600">{{ $order->name }}</p>
            @if($order->whatsapp)
                <p class="text-sm text-slate-500">{{ $order->whatsapp }}</p>
            @endif
            @if($order->address)
                <p class="mt-1 text-sm text-slate-500">{{ $order->address }}</p>
            @endif
            @if($order->notes)
                <p class="mt-1 text-sm text-slate-500">Catatan: {{ $order->notes }}</p>
            @endif
            <p class="mt-1 text-xs text-slate-400">Metode: {{ $order->payment_method }}</p>
        </div>
    </div>

    <p class="mt-6 text-center text-xs text-slate-400">
        <a href="{{ route('home') }}" class="text-emerald-600 hover:underline">← Kembali ke beranda</a>
    </p>
</div>
@endsection