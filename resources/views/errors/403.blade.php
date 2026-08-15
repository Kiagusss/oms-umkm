@extends('layouts.app')

@section('title', '403 — Akses Ditolak')

@section('content')
<section class="min-h-[70vh] flex items-center justify-center px-4 py-20">
  <div class="max-w-xl w-full text-center">
    <div class="text-[10rem] font-extrabold leading-none text-[var(--color-accent)] opacity-20 select-none">
      403
    </div>
    <h1 class="mt-4 text-3xl md:text-4xl font-bold text-[var(--color-ink)]">
      Akses Ditolak
    </h1>
    <p class="mt-3 text-base text-[var(--color-ink-3)]">
      Anda tidak memiliki izin untuk membuka halaman ini.
    </p>
    <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
      <a href="{{ route('home') }}"
         class="inline-flex items-center justify-center px-6 py-3 rounded-full bg-[var(--color-accent)] text-white font-semibold hover:opacity-90 transition">
        ← Kembali ke Beranda
      </a>
      <a href="https://wa.me/{{ config('app.wa_number', '6281234567890') }}?text={{ urlencode('Halo, saya butuh bantuan.') }}"
         target="_blank"
         rel="noopener"
         class="inline-flex items-center justify-center px-6 py-3 rounded-full border border-[var(--color-ink-3)] text-[var(--color-ink)] font-semibold hover:bg-stone-50 transition">
        💬 Hubungi via WhatsApp
      </a>
    </div>
  </div>
</section>
@endsection
