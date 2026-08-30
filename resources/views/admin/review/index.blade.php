@extends('admin.layouts.app')

@section('title', 'Ulasan Produk — Admin')

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Ulasan Produk',
    'description' => 'Moderasi ulasan dari pelanggan.',
])

@if(session('success'))
    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
@endif

<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <table class="min-w-full divide-y divide-slate-100 text-sm">
        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-400">
            <tr>
                <th class="px-4 py-3 text-left">Produk</th>
                <th class="px-4 py-3 text-left">Nama</th>
                <th class="px-4 py-3 text-left">Rating</th>
                <th class="px-4 py-3 text-left">Ulasan</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3 text-left">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($reviews as $review)
                <tr class="{{ $review->approved ? '' : 'bg-amber-50' }}">
                    <td class="px-4 py-3 font-medium text-slate-700">{{ $review->product?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $review->name }}</td>
                    <td class="px-4 py-3 text-amber-500">{{ str_repeat('★', $review->rating) }}</td>
                    <td class="px-4 py-3 text-slate-500 max-w-xs truncate">{{ $review->body ?: '—' }}</td>
                    <td class="px-4 py-3">
                        @if($review->approved)
                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">Disetujui</span>
                        @else
                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">Menunggu</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            @unless($review->approved)
                                <form method="POST" action="{{ route('admin.review.approve', $review) }}">
                                    @csrf @method('PATCH')
                                    <button class="rounded-lg bg-emerald-600 px-3 py-1 text-xs font-semibold text-white hover:bg-emerald-500">Setujui</button>
                                </form>
                            @endunless
                            <form method="POST" action="{{ route('admin.review.destroy', $review) }}"
                                  onsubmit="return confirm('Hapus ulasan ini?')">
                                @csrf @method('DELETE')
                                <button class="rounded-lg bg-red-100 px-3 py-1 text-xs font-semibold text-red-700 hover:bg-red-200">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">Belum ada ulasan.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-4 py-3">{{ $reviews->links() }}</div>
</div>
@endsection
