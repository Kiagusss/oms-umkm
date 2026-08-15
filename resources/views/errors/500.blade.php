@extends('layouts.app')

@section('title', '500 — Ada Kesalahan Server')

@section('content')
<section class="min-h-[70vh] flex items-center justify-center px-4 py-20">
  <div class="max-w-xl w-full text-center">
    <div class="text-[10rem] font-extrabold leading-none text-red-600 opacity-20 select-none">
      500
    </div>
    <h1 class="mt-4 text-3xl md:text-4xl font-bold text-[var(--color-ink)]">
      Ada Kesalahan di Server
    </h1>
    <p class="mt-3 text-base text-[var(--color-ink-3)]">
      Tim kami sudah mendapat notifikasi dan sedang memperbaiki. Silakan coba lagi beberapa saat.
    </p>
    <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
      <a href="{{ route('home') }}"
         class="inline-flex items-center justify-center px-6 py-3 rounded-full bg-[var(--color-accent)] text-white font-semibold hover:opacity-90 transition">
        ← Coba Lagi
      </a>
      <a href="https://wa.me/{{ config('app.wa_number', '6281234567890') }}?text={{ urlencode('Saya mengalami masalah saat membuka situs Anda.') }}"
         target="_blank"
         rel="noopener"
         class="inline-flex items-center justify-center px-6 py-3 rounded-full border border-[var(--color-ink-3)] text-[var(--color-ink)] font-semibold hover:bg-stone-50 transition">
        � Lapor via WhatsApp
      </a>
    </div>
    @if(config('app.debug'))
      <p class="mt-6 text-xs text-red-500">
        Mode debug aktif — detail error ada di <code>storage/logs/laravel.log</code>.
      </p>
    @endif
  </div>
</section>
@endsection
