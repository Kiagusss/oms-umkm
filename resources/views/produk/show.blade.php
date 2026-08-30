@extends('layouts.app')

@section('title', $product->name . ' — ' . config('app.name', 'Pempek Palembang'))

@section('content')
<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <nav class="mb-6 text-sm text-[var(--color-ink-3)]">
        <a href="{{ route('home') }}" class="hover:text-[var(--color-accent)]">Beranda</a>
        <span class="mx-2">/</span>
        @if($product->category)
            <a href="{{ route('kategori.show', $product->category->slug) }}" class="hover:text-[var(--color-accent)]">{{ $product->category->name }}</a>
            <span class="mx-2">/</span>
        @endif
        <span class="text-[var(--color-ink)]">{{ $product->name }}</span>
    </nav>

    <div class="grid gap-8 lg:grid-cols-2">
        <div class="overflow-hidden rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white">
            @if($product->thumbnail)
                <img src="{{ asset($product->thumbnail) }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
            @else
                <div class="flex h-full min-h-[300px] items-center justify-center bg-[var(--color-paper-2)] text-[var(--color-ink-3)]">Tidak ada gambar</div>
            @endif
        </div>

        <div class="flex flex-col justify-center">
            <h1 class="text-3xl font-bold tracking-tight text-[var(--color-ink)] sm:text-4xl">{{ $product->name }}</h1>
            <p class="mt-2 text-2xl font-bold text-[var(--color-accent)]">Rp{{ number_format($product->price, 0, ',', '.') }}</p>

            @if($product->description)
                <div class="prose mt-6 text-[var(--color-ink-2)]">{{ $product->description }}</div>
            @endif

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ $waLink }}" target="_blank"
                   class="rounded-[var(--radius-xl)] bg-[var(--color-accent)] px-6 py-3 text-center text-sm font-semibold text-white transition-all hover:bg-[var(--color-accent-hover)]">
                    Pesan via WhatsApp
                </a>
                <button onclick="window.dispatchEvent(new CustomEvent('pempek:add-to-cart', { detail: { id: {{ $product->id }}, name: '{{ $product->name }}', price: {{ $product->price }}, thumbnail: '{{ $product->thumbnail }}', stock: {{ $product->stock }} } }))"
                        class="rounded-[var(--radius-xl)] border border-[var(--color-accent)] px-6 py-3 text-center text-sm font-semibold text-[var(--color-accent)] hover:bg-[var(--color-accent-light)]">
                    + Keranjang
                </button>
            </div>

            @if($product->stock > 0)
                <p class="mt-4 text-sm text-[var(--color-ink-3)]">Stok: {{ $product->stock }}</p>
            @else
                <p class="mt-4 text-sm font-medium text-[var(--color-danger)]">Stok habis</p>
            @endif

            @include('partials.ongkir-widget')
        </div>
    </div>

    @if($related->count())
        <section class="mt-14">
            <h2 class="mb-4 text-xl font-bold text-[var(--color-ink)]">Produk Serupa</h2>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                @foreach($related as $p)
                    <a href="{{ route('produk.show', $p->slug) }}" class="group overflow-hidden rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white transition-all hover:shadow-md">
                        @if($p->thumbnail)
                            <img src="{{ asset($p->thumbnail) }}" alt="{{ $p->name }}" class="h-40 w-full object-cover">
                        @endif
                        <div class="p-3">
                            <p class="truncate text-sm font-semibold text-[var(--color-ink)]">{{ $p->name }}</p>
                            <p class="text-sm font-bold text-[var(--color-accent)]">Rp{{ number_format($p->price, 0, ',', '.') }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Rating & Review --}}
    <section class="mt-14" id="reviews">
        <h2 class="mb-1 text-xl font-bold text-[var(--color-ink)]">Rating & Ulasan</h2>
        @if($avgRating)
            <p class="mb-4 text-sm text-[var(--color-ink-3)]">
                <span class="text-2xl font-bold text-amber-500">{{ number_format($avgRating, 1) }}</span>
                / 5 &nbsp;·&nbsp; {{ $reviews->count() }} ulasan
            </p>
        @endif

        @if(session('review_sent'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                Terima kasih! Ulasanmu sedang menunggu moderasi.
            </div>
        @endif

        {{-- Form --}}
        <form method="POST" action="{{ route('produk.review', $product->slug) }}"
              class="mb-8 rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white p-5 shadow-sm">
            @csrf
            <h3 class="mb-3 text-sm font-semibold text-[var(--color-ink)]">Tulis Ulasan</h3>
            @if($errors->any())
                <p class="mb-3 text-sm text-[var(--color-danger)]">{{ $errors->first() }}</p>
            @endif
            <div class="mb-3">
                <label class="mb-1 block text-xs font-medium text-[var(--color-ink-3)]">Nama</label>
                <input name="name" type="text" required maxlength="80" value="{{ old('name') }}"
                       class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm focus:border-[var(--color-accent)] focus:outline-none">
            </div>
            <div class="mb-3">
                <label class="mb-1 block text-xs font-medium text-[var(--color-ink-3)]">Rating</label>
                <div class="flex gap-1" x-data="{ r: {{ old('rating', 0) }} }">
                    @for($s = 1; $s <= 5; $s++)
                        <button type="button" @click="r = {{ $s }}"
                                :class="r >= {{ $s }} ? 'text-amber-400' : 'text-slate-300'"
                                class="text-2xl leading-none transition-colors">★</button>
                    @endfor
                    <input type="hidden" name="rating" :value="r">
                </div>
            </div>
            <div class="mb-4">
                <label class="mb-1 block text-xs font-medium text-[var(--color-ink-3)]">Ulasan (opsional)</label>
                <textarea name="body" rows="3" maxlength="1000"
                          class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm focus:border-[var(--color-accent)] focus:outline-none">{{ old('body') }}</textarea>
            </div>
            <button type="submit"
                    class="rounded-[var(--radius-md)] bg-[var(--color-accent)] px-5 py-2 text-sm font-semibold text-white hover:bg-[var(--color-accent-hover)]">
                Kirim Ulasan
            </button>
        </form>

        {{-- Daftar review --}}
        @forelse($reviews as $review)
            <div class="mb-4 rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white p-4">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-[var(--color-ink)]">{{ $review->name }}</p>
                    <span class="text-amber-400 text-sm">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                </div>
                @if($review->body)
                    <p class="mt-1 text-sm text-[var(--color-ink-2)]">{{ $review->body }}</p>
                @endif
                <p class="mt-1 text-xs text-[var(--color-ink-3)]">{{ $review->created_at->translatedFormat('d F Y') }}</p>
            </div>
        @empty
            <p class="text-sm text-[var(--color-ink-3)]">Belum ada ulasan. Jadilah yang pertama!</p>
        @endforelse
    </section>
</div>
@endsection
