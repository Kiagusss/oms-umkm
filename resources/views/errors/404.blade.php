@extends('layouts.app')

@section('title', '404 — Halaman Tidak Ditemukan')

@section('content')
<section class="min-h-[70vh] flex items-center justify-center px-4 py-20">
  <div class="max-w-xl w-full text-center">
    <div class="text-[10rem] font-extrabold leading-none text-[var(--color-accent)] opacity-20 select-none">
      404
    </div>
    <h1 class="mt-4 text-3xl md:text-4xl font-bold text-[var(--color-ink)]">
      Halaman Tidak Ditemukan
    </h1>
    <p class="mt-3 text-base text-[var(--color-ink-3)]">
      Halaman yang Anda cari mungkin sudah dipindahkan, dihapus, atau tidak pernah ada.
    </p>
    <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
      <a href="{{ route('home') }}"
         class="inline-flex items-center justify-center px-6 py-3 rounded-full bg-[var(--color-accent)] text-white font-semibold hover:opacity-90 transition">
        ← Kembali ke Beranda
      </a>
      <a href="{{ route('artikel.index') }}"
         class="inline-flex items-center justify-center px-6 py-3 rounded-full border border-[var(--color-ink-3)] text-[var(--color-ink)] font-semibold hover:bg-stone-50 transition">
        📚 Lihat Artikel
      </a>
    </div>
  </div>
</section>
@endsection
