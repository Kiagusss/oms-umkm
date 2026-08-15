@extends('layouts.app')

@section('title', '503 — Sedang Pemeliharaan')

@section('content')
<section class="min-h-[70vh] flex items-center justify-center px-4 py-20">
  <div class="max-w-xl w-full text-center">
    <div class="text-[8rem]">🔧</div>
    <h1 class="mt-4 text-3xl md:text-4xl font-bold text-[var(--color-ink)]">
      Sedang Pemeliharaan
    </h1>
    <p class="mt-3 text-base text-[var(--color-ink-3)]">
      Kami sedang melakukan pemeliharaan rutin untuk meningkatkan kualitas layanan. Kami akan segera kembali.
    </p>
    <div class="mt-8">
      <a href="https://wa.me/{{ config('app.wa_number', '6281234567890') }}?text={{ urlencode('Halo, apakah situs sedang down?') }}"
         target="_blank"
         rel="noopener"
         class="inline-flex items-center justify-center px-6 py-3 rounded-full bg-[var(--color-accent)] text-white font-semibold hover:opacity-90 transition">
        💬 Tanya via WhatsApp
      </a>
    </div>
  </div>
</section>
@endsection
