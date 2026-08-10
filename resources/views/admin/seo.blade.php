@extends('admin.layouts.app')

@section('title', 'Optimasi SEO — Admin Pempek')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold tracking-tight text-[var(--color-ink)]">Optimasi SEO</h1>
    <p class="mt-1 text-sm text-[var(--color-ink-3)]">Kelola metadata global, indexing bot pencari, favicon, verifikasi kepemilikan, dan skema JSON-LD.</p>
</div>

<div class="max-w-3xl rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white p-6 sm:p-8">
    <form method="POST" action="{{ route('admin.seo.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Nama Situs</label>
                <input type="text" name="site_name" value="{{ $seo->site_name ?? '' }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">URL Situs</label>
                <input type="text" name="site_url" value="{{ $seo->site_url ?? '' }}" placeholder="https://domainanda.com" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Global Meta Title</label>
            <input type="text" name="default_title" required value="{{ $seo->default_title ?? '' }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Global Meta Description</label>
            <textarea name="default_description" rows="3" required class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">{{ $seo->default_description ?? '' }}</textarea>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Global Keywords (pisahkan dengan koma)</label>
            <input type="text" name="keywords" value="{{ $seo->keywords ?? '' }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">URL OG Image</label>
                <input type="text" name="og_image" value="{{ $seo->og_image ?? '' }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Favicon</label>
                <input type="text" name="favicon" value="{{ $seo->favicon ?? '' }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Canonical URL</label>
                <input type="text" name="canonical_url" value="{{ $seo->canonical_url ?? '' }}" placeholder="https://domainanda.com" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Robots Meta Directive</label>
                <input type="text" name="robots" value="{{ $seo->robots ?? 'index, follow' }}" placeholder="index, follow" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Google Site Verification Code</label>
            <input type="text" name="google_verification" value="{{ $seo->google_verification ?? '' }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Skema Tambahan JSON-LD (Script Tag)</label>
            <textarea name="schema_json_ld" rows="5" placeholder="contoh: JSON-LD schema.org (tanpa kurung kurawal pembuka di atribut)" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 font-mono text-xs">{{ $seo->schema_json_ld ?? '' }}</textarea>
        </div>

        <div class="flex justify-end border-t border-[var(--color-paper-3)] pt-6">
            <button type="submit" class="rounded-[var(--radius-xl)] bg-[var(--color-accent)] px-6 py-2.5 text-sm font-semibold text-white transition-all hover:bg-[var(--color-accent-hover)]">
                Simpan Optimasi SEO
            </button>
        </div>
    </form>
</div>
@endsection
