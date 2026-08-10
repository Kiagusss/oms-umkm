@extends('admin.layouts.app')

@section('title', 'Pengaturan Website — Admin Pempek')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold tracking-tight text-[var(--color-ink)]">Pengaturan Website</h1>
    <p class="mt-1 text-sm text-[var(--color-ink-3)]">Kelola metadata inti, kontak WhatsApp, jam operasional, media sosial, dan peta lokasi.</p>
</div>

<div class="max-w-3xl rounded-[var(--radius-xl)] border border-[var(--color-paper-3)] bg-white p-6 sm:p-8">
    <form method="POST" action="{{ route('admin.pengaturan.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Nama Website</label>
                <input type="text" name="site_name" required value="{{ $settings['site_name'] ?? '' }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Email Kontak</label>
                <input type="email" name="email" required value="{{ $settings['email'] ?? '' }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Logo URL</label>
                <input type="text" name="logo" value="{{ $settings['logo'] ?? '' }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Nomor WhatsApp</label>
                <input type="text" name="whatsapp" required value="{{ $settings['whatsapp'] ?? '' }}" placeholder="628123456789" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Jam Operasional</label>
                <input type="text" name="operating_hours" required value="{{ $settings['operating_hours'] ?? '' }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Teks Hak Cipta Footer</label>
                <input type="text" name="footer_text" required value="{{ $settings['footer_text'] ?? '' }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Alamat Kantor / Toko</label>
            <textarea name="address" rows="2" required class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">{{ $settings['address'] ?? '' }}</textarea>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Tentang Kami (Profil Halaman Depan)</label>
            <textarea name="about_us" rows="4" required class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">{{ $settings['about_us'] ?? '' }}</textarea>
        </div>

        <div class="grid gap-6 sm:grid-cols-3">
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Username Instagram</label>
                <input type="text" name="instagram" value="{{ $settings['instagram'] ?? '' }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Username Facebook</label>
                <input type="text" name="facebook" value="{{ $settings['facebook'] ?? '' }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Username TikTok</label>
                <input type="text" name="tiktok" value="{{ $settings['tiktok'] ?? '' }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
            </div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold text-[var(--color-ink)]">Google Maps Embed URL</label>
            <input type="text" name="google_maps_embed" value="{{ $settings['google_maps_embed'] ?? '' }}" class="w-full rounded-[var(--radius-md)] border border-[var(--color-paper-3)] px-3 py-2 text-sm">
        </div>

        <div class="flex justify-end border-t border-[var(--color-paper-3)] pt-6">
            <button type="submit" class="rounded-[var(--radius-xl)] bg-[var(--color-accent)] px-6 py-2.5 text-sm font-semibold text-white transition-all hover:bg-[var(--color-accent-hover)]">
                Simpan Pengaturan
            </button>
        </div>
    </form>
</div>
@endsection
