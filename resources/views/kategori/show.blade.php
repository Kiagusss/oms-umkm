@extends('layouts.app')

@section('title', $category->name . ' — ' . config('app.name', 'Pempek Palembang'))

@section('content')
<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <nav class="mb-6 text-sm text-[var(--color-ink-3)]">
        <a href="{{ route('home') }}" class="hover:text-[var(--color-accent)]">Beranda</a>
        <span class="mx-2">/</span>
        <span class="text-[var(--color-ink)]">{{ $category->name }}</span>
    </nav>

    <div class="mb-8">
        <h1 class="text-3xl font-bold tracking-tight text-[var(--color-ink)] sm:text-4xl">{{ $category->name }}</h1>
        @if($category->description)
            <p class="mt-2 max-w-2xl text-[var(--color-ink-2)]">{{ $category->description }}</p>
        @endif
    </div>

    @if($products->count())
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            @foreach($products as $product)
                <a href="{{ route('produk.show', $product->slug) }}" class="group overflow-hidden rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white transition-all hover:shadow-md">
                    @if($product->thumbnail)
                        <img src="{{ asset($product->thumbnail) }}" alt="{{ $product->name }}" class="h-44 w-full object-cover">
                    @endif
                    <div class="p-3">
                        <p class="truncate text-sm font-semibold text-[var(--color-ink)]">{{ $product->name }}</p>
                        <p class="text-sm font-bold text-[var(--color-accent)]">Rp{{ number_format($product->price, 0, ',', '.') }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <p class="rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white p-10 text-center text-[var(--color-ink-3)]">
            Belum ada produk di kategori ini.
        </p>
    @endif
</div>
@endsection
