@extends('admin.layouts.app')

@section('title', 'Produk — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Produk',
    'description' => 'Kelola menu pempek, harga, stok, dan status.',
    'actionRoute' => route('admin.produk.create'),
    'actionLabel' => 'Tambah Produk',
])

<div class="mb-5 flex flex-col gap-3 sm:flex-row">
    <form method="GET" class="flex flex-1 gap-2">
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama produk..." class="flex-1 rounded-[var(--radius-lg)] border border-[var(--color-paper-3)] bg-white px-4 py-2.5 text-sm">
        <select name="status" class="rounded-[var(--radius-lg)] border border-[var(--color-paper-3)] bg-white px-3 py-2.5 text-sm">
            <option value="">Semua Status</option>
            <option value="active" @selected(request('status') === 'active')>Aktif</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option>
        </select>
        <button class="rounded-[var(--radius-lg)] bg-[var(--color-accent)] px-4 py-2.5 text-sm font-semibold text-white">Cari</button>
    </form>
</div>

<div class="overflow-x-auto rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white">
    <table class="w-full border-collapse text-left text-sm">
        <thead>
            <tr class="border-b border-[var(--color-paper-3)] bg-[var(--color-paper-2)] font-semibold text-[var(--color-ink)]">
                <th class="px-6 py-4">Produk</th>
                <th class="px-6 py-4">Kategori</th>
                <th class="px-6 py-4">Harga</th>
                <th class="px-6 py-4">Stok</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[var(--color-paper-3)] text-[var(--color-ink-2)]">
            @forelse($products as $product)
                <tr class="hover:bg-[var(--color-paper-2)] transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            @if($product->thumbnail)
                                <img src="{{ asset($product->thumbnail) }}" alt="" class="h-11 w-11 rounded-[var(--radius-md)] object-cover">
                            @endif
                            <div>
                                <span class="block font-semibold text-[var(--color-ink)]">{{ $product->name }}</span>
                                @if($product->is_best_seller)<span class="text-xs text-[var(--color-accent)]">Best seller</span>@endif
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">{{ $product->category?->name ?? '—' }}</td>
                    <td class="px-6 py-4 font-semibold tabular-nums text-[var(--color-ink)]">Rp{{ number_format($product->price) }}</td>
                    <td class="px-6 py-4 tabular-nums">{{ $product->stock }}</td>
                    <td class="px-6 py-4">
                        <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $product->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $product->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.produk.edit', $product) }}" class="rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-1.5 text-xs font-medium text-[var(--color-accent)] hover:bg-[var(--color-accent-light)]">Edit</a>
                            <form method="POST" action="{{ route('admin.produk.destroy', $product) }}" onsubmit="return confirm('Hapus produk ini?')">
                                @csrf @method('DELETE')
                                <button class="rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-1.5 text-xs font-medium text-[var(--color-danger)] hover:bg-red-50">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-6 py-10 text-center text-[var(--color-ink-3)]">Belum ada produk.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-5">{{ $products->links() }}</div>
@endsection
