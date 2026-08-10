@extends('admin.layouts.app')

@section('title', 'Pesanan — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Pesanan',
    'description' => 'Kelola daftar pesanan pelanggan dan statusnya.',
    'actionRoute' => route('admin.pesanan.create'),
    'actionLabel' => 'Tambah Pesanan',
])

{{-- Statistik status --}}
<div class="mb-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
    @foreach([
        ['Total', $stats['total'], 'bg-[var(--color-paper-2)] text-[var(--color-ink)]'],
        ['Pending', $stats['pending'], 'bg-amber-50 text-amber-700'],
        ['Diproses', $stats['processing'], 'bg-blue-50 text-blue-700'],
        ['Selesai', $stats['completed'], 'bg-emerald-50 text-emerald-700'],
        ['Dibatalkan', $stats['cancelled'], 'bg-red-50 text-red-700'],
    ] as [$label, $value, $cls])
        <div class="rounded-[var(--radius-lg)] border border-[var(--color-paper-3)] bg-white px-4 py-3">
            <p class="text-xs font-medium text-[var(--color-ink-3)]">{{ $label }}</p>
            <p class="mt-1 text-2xl font-bold tabular-nums {{ $cls }}">{{ $value }}</p>
        </div>
    @endforeach
</div>

{{-- Filter --}}
<form method="GET" class="mb-5 flex flex-col gap-2 sm:flex-row">
    <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama / WhatsApp..." class="flex-1 rounded-[var(--radius-lg)] border border-[var(--color-paper-3)] bg-white px-4 py-2.5 text-sm">
    <select name="status" class="rounded-[var(--radius-lg)] border border-[var(--color-paper-3)] bg-white px-3 py-2.5 text-sm">
        <option value="">Semua Status</option>
        @foreach(['pending' => 'Pending', 'processing' => 'Diproses', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'] as $v => $l)
            <option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>
        @endforeach
    </select>
    <button class="rounded-[var(--radius-lg)] bg-[var(--color-accent)] px-4 py-2.5 text-sm font-semibold text-white">Filter</button>
</form>

<div class="overflow-x-auto rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white">
    <table class="w-full border-collapse text-left text-sm">
        <thead>
            <tr class="border-b border-[var(--color-paper-3)] bg-[var(--color-paper-2)] font-semibold text-[var(--color-ink)]">
                <th class="px-6 py-4">#</th>
                <th class="px-6 py-4">Pelanggan</th>
                <th class="px-6 py-4">Tanggal</th>
                <th class="px-6 py-4">Total</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[var(--color-paper-3)] text-[var(--color-ink-2)]">
            @forelse($orders as $order)
                <tr class="hover:bg-[var(--color-paper-2)] transition-colors">
                    <td class="px-6 py-4 font-semibold text-[var(--color-ink)]">#{{ $order->id }}</td>
                    <td class="px-6 py-4">
                        <span class="block font-semibold text-[var(--color-ink)]">{{ $order->name }}</span>
                        @if($order->whatsapp)
                            <span class="text-xs text-[var(--color-ink-3)]">{{ $order->whatsapp }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">{{ \Carbon\Carbon::parse($order->date)->translatedFormat('d M Y') }}</td>
                    <td class="px-6 py-4 font-semibold tabular-nums text-[var(--color-ink)]">
                        Rp{{ number_format(collect(json_decode($order->products ?? '[]', true))->sum(fn ($i) => $i['price'] * $i['quantity'])) }}
                    </td>
                    <td class="px-6 py-4">
                        <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $order->status === 'pending' ? 'bg-amber-50 text-amber-700' : ($order->status === 'processing' ? 'bg-blue-50 text-blue-700' : ($order->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700')) }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.pesanan.show', $order) }}" class="rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-1.5 text-xs font-medium text-[var(--color-ink-2)] hover:bg-[var(--color-paper-2)]">Detail</a>
                            <a href="{{ route('admin.pesanan.edit', $order) }}" class="rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-1.5 text-xs font-medium text-[var(--color-accent)] hover:bg-[var(--color-accent-light)]">Edit</a>
                            <form method="POST" action="{{ route('admin.pesanan.destroy', $order) }}" onsubmit="return confirm('Hapus pesanan ini?')">
                                @csrf @method('DELETE')
                                <button class="rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-1.5 text-xs font-medium text-[var(--color-danger)] hover:bg-red-50">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-6 py-10 text-center text-[var(--color-ink-3)]">Belum ada pesanan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-5">{{ $orders->links() }}</div>
@endsection
