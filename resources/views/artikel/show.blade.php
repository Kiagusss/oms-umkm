@extends('layouts.app')

@section('content')
@php
    $articleDate = \Carbon\Carbon::parse($article->date)->translatedFormat('d F Y');
@endphp

<article class="bg-[var(--color-paper)] py-12 sm:py-16 lg:py-20">
    <div class="mx-auto max-w-3xl px-5 sm:px-8">
        {{-- Header --}}
        <header class="mb-8">
            <div class="mb-3 flex items-center gap-2 text-sm text-[var(--color-ink-3)]">
                <time datetime="{{ $article->date }}">{{ $articleDate }}</time>
                <span aria-hidden="true">·</span>
                <span>{{ $article->category }}</span>
                @if($article->author)
                <span aria-hidden="true">·</span>
                <span>{{ $article->author }}</span>
                @endif
            </div>
            <h1 class="text-2xl font-bold tracking-tight leading-tight sm:text-3xl lg:text-4xl" style="color: var(--color-ink)">{{ $article->title }}</h1>
        </header>

        {{-- Thumbnail --}}
        @if($article->thumbnail)
        <div class="mb-8 overflow-hidden rounded-[var(--radius-xl)]">
            <img src="{{ asset($article->thumbnail) }}" alt="{{ $article->title }}" class="w-full h-auto">
        </div>
        @endif

        {{-- Content --}}
        <div class="prose prose-teal max-w-none" style="--tw-prose-body: var(--color-ink-2); --tw-prose-headings: var(--color-ink); --tw-prose-links: var(--color-accent); --tw-prose-bold: var(--color-ink);">
            {!! $article->content !!}
        </div>

        {{-- Share / CTA --}}
        <div class="mt-10 rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white p-6">
            <p class="font-semibold text-[var(--color-ink)]">Suka artikel ini?</p>
            <p class="mt-2 text-[var(--color-ink-2)]">Bagikan ke teman-teman atau pesan pempek favorit Anda sekarang.</p>
            <a
                href="{{ $waLink }}"
                target="_blank"
                rel="noopener noreferrer"
                class="mt-4 inline-flex items-center gap-2 rounded-[var(--radius-xl)] bg-[var(--color-accent)] px-6 py-2.5 text-sm font-semibold text-white transition-all duration-[var(--dur-normal)] hover:bg-[var(--color-accent-hover)]"
            >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                Pesan via WhatsApp
            </a>
        </div>
    </div>
</article>

{{-- Related articles --}}
@if($related->count() > 0)
<section class="bg-[var(--color-paper-2)] py-12 sm:py-16">
    <div class="mx-auto max-w-7xl px-5 sm:px-8">
        <h2 class="mb-8 text-xl font-bold text-[var(--color-ink)] sm:text-2xl">Artikel Terkait</h2>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($related as $rel)
            <a
                href="{{ route('artikel.show', $rel->slug) }}"
                class="group overflow-hidden rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white transition-all duration-[var(--dur-slow)] hover:shadow-[var(--shadow-md)] hover:-translate-y-0.5"
            >
                <div class="relative aspect-[16/9] overflow-hidden bg-[var(--color-paper-2)]">
                    <img
                        src="{{ asset($rel->thumbnail) }}"
                        alt="{{ $rel->title }}"
                        class="h-full w-full object-cover transition-transform duration-[var(--dur-slow)] group-hover:scale-105"
                    >
                </div>
                <div class="p-4">
                    <time datetime="{{ $rel->date }}" class="text-xs text-[var(--color-ink-3)]">
                        {{ \Carbon\Carbon::parse($rel->date)->translatedFormat('d F Y') }}
                    </time>
                    <h3 class="mt-1 line-clamp-2 text-base font-semibold text-[var(--color-ink)] transition-colors group-hover:text-[var(--color-accent)]">{{ $rel->title }}</h3>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tailwindcss/typography@0.5.16/dist/typography.css">
@endpush