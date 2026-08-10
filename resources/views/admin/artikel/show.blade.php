@extends('admin.layouts.app')

@section('title', $artikel->title . ' — Admin Pempek')

@section('content')
@include('admin.partials.page-header', [
    'title' => $artikel->title,
    'description' => $artikel->category . ' • ' . \Carbon\Carbon::parse($artikel->date)->translatedFormat('d M Y'),
    'actionRoute' => route('admin.artikel.edit', $artikel),
    'actionLabel' => 'Edit Artikel',
])

<div class="grid gap-6 lg:grid-cols-3">
    <div class="prose max-w-none rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white p-6 lg:col-span-2">
        @if($artikel->thumbnail)
            <img src="{{ asset($artikel->thumbnail) }}" alt="{{ $artikel->title }}" class="mb-6 w-full rounded-[var(--radius-lg)] object-cover">
        @endif
        {!! $artikel->content !!}
    </div>
    <aside class="space-y-4">
        <div class="rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white p-6">
            <h2 class="mb-3 text-sm font-semibold text-[var(--color-ink)]">Info Artikel</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-[var(--color-ink-3)]">Penulis</dt><dd>{{ $artikel->author ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-[var(--color-ink-3)]">Tanggal</dt><dd>{{ $artikel->date }}</dd></div>
                <div class="flex justify-between"><dt class="text-[var(--color-ink-3)]">Status</dt><dd>{{ ucfirst($artikel->status) }}</dd></div>
            </dl>
        </div>
        <a href="{{ route('admin.artikel.preview', $artikel) }}" target="_blank"
           class="flex w-full items-center justify-center gap-2 rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white py-2.5 text-sm font-semibold text-[var(--color-accent)] hover:bg-[var(--color-accent-light)]">
            Lihat Preview
        </a>
    </aside>
</div>
@endsection
