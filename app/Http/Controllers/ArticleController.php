<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $articles = Article::where('status', 'published')
            ->orderBy('date', 'desc')
            ->paginate(9);

        return view('artikel.index', [
            'articles' => $articles,
            'seoTitle' => 'Artikel Pempek Palembang — Tips, Resep & Cerita',
            'seoDescription' => 'Kumpulan artikel seputar pempek Palembang: resep, cara penyimpanan, tips memilih pempek asli, dan cerita kuliner khas Sumatera Selatan.',
            'metaKeywords' => 'artikel pempek, resep pempek, pempek palembang, tips pempek, oleh oleh palembang',
        ]);
    }

    public function show($slug)
    {
        $article = Article::where('slug', $slug)->where('status', 'published')->firstOrFail();

        $related = Article::where('status', 'published')
            ->where('id', '!=', $article->id)
            ->orderBy('date', 'desc')
            ->take(3)
            ->get();

        $title = $article->seo_title ?: $article->title . ' — Pempek Palembang';
        $description = $article->seo_description
            ?: mb_substr(strip_tags($article->content), 0, 155);

        $image = $article->thumbnail ? asset($article->thumbnail) : asset('images/hero-pempek.png');

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $article->title,
            'image' => $image,
            'datePublished' => optional($article->date)->toIso8601String(),
            'dateModified' => optional($article->updated_at)->toIso8601String() ?? optional($article->date)->toIso8601String(),
            'author' => ['@type' => 'Organization', 'name' => config('app.name', 'Pempek Palembang')],
            'publisher' => ['@type' => 'Organization', 'name' => config('app.name', 'Pempek Palembang'), 'logo' => ['@type' => 'ImageObject', 'url' => asset('images/hero-pempek.png')]],
            'mainEntityOfPage' => url()->current(),
            'inLanguage' => 'id-ID',
        ];
        if ($article->category) {
            $schema['articleSection'] = $article->category;
        }

        return view('artikel.show', [
            'article' => $article,
            'related' => $related,
            'seoTitle' => $title,
            'seoDescription' => $description,
            'metaKeywords' => $article->meta_keywords ?: 'artikel pempek, ' . strtolower($article->title),
            'ogType' => 'article',
            'ogTitle' => $title,
            'ogDescription' => $description,
            'ogImage' => $image,
            'schemaJsonLd' => json_encode($schema, JSON_UNESCAPED_SLASHES),
        ]);
    }
}
