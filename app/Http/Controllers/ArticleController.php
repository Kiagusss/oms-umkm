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

        return view('artikel.show', [
            'article' => $article,
            'related' => $related,
        ]);
    }
}
