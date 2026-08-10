@extends('layouts.app')

@section('content')
    @include('sections.hero', ['banners' => $banners, 'waLink' => $waLink])
    @include('sections.keunggulan')
    @include('sections.tentang')
    @include('sections.produk', ['products' => $products, 'waLink' => $waLink])
    @include('sections.paket-hemat', ['packages' => $packages])
    @include('sections.cara-pemesanan')
    @include('sections.testimoni', ['testimonials' => $testimonials])
    @include('sections.faq', ['faqs' => $faqs])
    @include('sections.artikel', ['articles' => $articles])
    @include('sections.cta', ['waLink' => $waLink])
@endsection
