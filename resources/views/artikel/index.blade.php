@extends('layouts.app')

@section('content')
<section class="bg-[var(--color-paper)] py-12 sm:py-16 lg:py-20">
    <div class="mx-auto max-w-7xl px-5 sm:px-8">
        <div class="mb-10 sm:mb-14">
            <h1 class="text-2xl font-bold tracking-tight sm:text-3xl lg:text-4xl" style="color: var(--color-ink)">Artikel</h1>
            <p class="mt-3 max-w-2xl text-base text-[var(--color-ink-2)] sm:text-lg">Tips, resep, dan cerita seputar pempek Palembang.</p>
        </div>

        @if($articles->count() > 0)
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($articles as $article)
            <a
                href="{{ route('artikel.show', $article->slug) }}"
                class="group overflow-hidden rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white transition-all duration-[var(--dur-slow)] hover:shadow-[var(--shadow-md)] hover:-translate-y-0.5"
            >
                <div class="relative aspect-[16/9] overflow-hidden bg-[var(--color-paper-2)]">
                    <img
                        src="{{ asset($article->thumbnail) }}"
                        alt="{{ $article->title }}"
                        class="h-full w-full object-cover transition-transform duration-[var(--dur-slow)] group-hover:scale-105"
                    >
                    <div class="absolute top-3 left-3">
                        <span class="rounded-[var(--radius-md)] bg-white/90 px-2.5 py-1 text-xs font-medium text-[var(--color-ink-2)] backdrop-blur-sm">{{ $article->category }}</span>
                    </div>
                </div>

                <div class="p-5">
                    <time datetime="{{ $article->date }}" class="text-xs text-[var(--color-ink-3)]">
                        {{ \Carbon\Carbon::parse($article->date)->translatedFormat('d F Y') }}
                    </time>
                    <h3 class="mt-2 line-clamp-2 text-base font-semibold leading-snug text-[var(--color-ink)] transition-colors duration-[var(--dur-normal)] group-hover:text-[var(--color-accent)]">{{ $article->title }}</h3>
                    <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-[var(--color-ink-2)]">{!! \Illuminate\Support\Str::limit(strip_tags($article->content), 120) !!}</p>
                    <span class="mt-4 inline-flex items-center gap-1 text-sm font-medium text-[var(--color-accent)]">
                        Baca selengkapnya
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" aria-hidden="true">
                            <path d="M5 12h14M12 5l7 7-7 7" />
                        </svg>
                    </span>
                </div>
            </a>
            @endforeach
        </div>

        {{ $articles->links() }}
        @else
        <div class="text-center py-12">
            <p class="text-[var(--color-ink-2)]">Belum ada artikel tersedia.</p>
        </div>
        @endif
    </div>
</section>
@endsection