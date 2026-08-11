<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Struk #{{ $pesanan->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #111; width: 100%; }
        .header { text-align: center; border-bottom: 2px dashed #111; padding-bottom: 8px; margin-bottom: 10px; }
        .header h1 { font-size: 16px; text-transform: uppercase; letter-spacing: 1px; }
        .header p { font-size: 10px; color: #333; margin-top: 2px; }
        .title { text-align: center; font-size: 13px; font-weight: bold; margin-bottom: 10px; letter-spacing: 3px; }
        .meta { width: 100%; margin-bottom: 10px; }
        .meta td { padding: 1px 0; vertical-align: top; }
        .meta .label { color: #555; }
        table.items { width: 100%; border-collapse: collapse; }
        table.items th { text-align: left; font-size: 10px; text-transform: uppercase; border-bottom: 1px solid #111; padding: 4px 0; }
        table.items td { padding: 4px 0; vertical-align: top; }
        table.items td.r, table.items th.r { text-align: right; }
        .totals { width: 100%; margin-top: 8px; border-top: 1px dashed #111; padding-top: 6px; }
        .totals td { padding: 2px 0; }
        .totals td.r { text-align: right; }
        .totals .grand td { font-size: 14px; font-weight: bold; border-top: 2px solid #111; padding-top: 6px; }
        .footer { text-align: center; margin-top: 18px; border-top: 2px dashed #111; padding-top: 8px; font-size: 10px; color: #333; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $settings['site_name'] ?? '' }}</h1>
        <p>{{ $settings['address'] ?? '' }}</p>
    </div>

    <div class="title">STRUK PEMBAYARAN</div>

    <table class="meta">
        <tr>
            <td class="label">No. Order</td>
            <td>#{{ $pesanan->id }}</td>
            <td class="label">Tanggal</td>
            <td>{{ \Carbon\Carbon::parse($pesanan->date)->translatedFormat('d M Y') }}</td>
        </tr>
        <tr>
            <td class="label">Pelanggan</td>
            <td colspan="3">{{ $pesanan->name }}</td>
        </tr>
        <tr>
            <td class="label">WhatsApp</td>
            <td colspan="3">{{ $pesanan->whatsapp }}</td>
        </tr>
        @if($pesanan->notes)
        <tr>
            <td class="label">Catatan</td>
            <td colspan="3">{{ $pesanan->notes }}</td>
        </tr>
        @endif
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Item</th>
                <th class="r">Qty</th>
                <th class="r">Harga</th>
                <th class="r">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    <td>{{ $item['productName'] ?? $item['name'] ?? '-' }}</td>
                    <td class="r">{{ $item['quantity'] }}</td>
                    <td class="r">{{ number_format($item['price'] ?? 0) }}</td>
                    <td class="r">{{ number_format(($item['price'] ?? 0) * $item['quantity']) }}</td>
                </tr>
            @empty
                <tr><td colspan="4">Tidak ada item.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>Subtotal</td>
            <td class="r">Rp{{ number_format($subtotal) }}</td>
        </tr>
        @if($discount > 0)
        <tr>
            <td>Diskon</td>
            <td class="r">-Rp{{ number_format($discount) }}</td>
        </tr>
        @endif
        <tr class="grand">
            <td>TOTAL</td>
            <td class="r">Rp{{ number_format($subtotal - $discount) }}</td>
        </tr>
        @if($pesanan->payment_method)
        <tr>
            <td>Metode Bayar</td>
            <td class="r">{{ strtoupper($pesanan->payment_method) }}</td>
        </tr>
        @endif
        @if($pesanan->cash_received !== null)
        <tr>
            <td>Tunai</td>
            <td class="r">Rp{{ number_format((int) $pesanan->cash_received) }}</td>
        </tr>
        @endif
        @if($pesanan->change_amount !== null)
        <tr>
            <td>Kembalian</td>
            <td class="r">Rp{{ number_format((int) $pesanan->change_amount) }}</td>
        </tr>
        @endif
    </table>

    <div class="footer">
        Terima kasih atas kunjungan Anda!<br>
        @if(!empty($settings['whatsapp']))
            WhatsApp: {{ $settings['whatsapp'] }}
        @endif
    </div>
</body>
</html>
