{{-- Admin page header — @include('admin.partials.page-header', ['title' => ..., 'description' => ..., 'actionRoute' => ..., 'actionLabel' => ...]) --}}
<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[var(--color-ink)]">{{ $title }}</h1>
        <p class="mt-1 text-sm text-[var(--color-ink-3)]">{{ $description ?? '' }}</p>
    </div>
    @if(!empty($actionRoute))
        <a href="{{ $actionRoute }}" class="inline-flex shrink-0 items-center gap-2 rounded-[var(--radius-xl)] bg-[var(--color-accent)] px-5 py-2.5 text-sm font-semibold text-white transition-all hover:bg-[var(--color-accent-hover)]">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14" /></svg>
            {{ $actionLabel ?? 'Tambah' }}
        </a>
    @endif
</div>
