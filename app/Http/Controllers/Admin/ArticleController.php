<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::query();

        if ($search = $request->get('search')) {
            $query->where('title', 'like', "%{$search}%");
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $articles = $query->orderBy('date', 'desc')->paginate(15);

        return view('admin.artikel.index', compact('articles'));
    }

    public function create()
    {
        return view('admin.artikel.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateArticle($request);

        Article::create($validated);

        return redirect()->route('admin.artikel.index')->with('success', 'Artikel berhasil ditambahkan.');
    }

    public function show(Article $artikel)
    {
        return view('admin.artikel.show', compact('artikel'));
    }

    public function edit(Article $artikel)
    {
        return view('admin.artikel.edit', compact('artikel'));
    }

    public function update(Request $request, Article $artikel)
    {
        $validated = $this->validateArticle($request, $artikel);

        $artikel->update($validated);

        return redirect()->route('admin.artikel.index')->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Article $artikel)
    {
        $artikel->delete();

        return redirect()->route('admin.artikel.index')->with('success', 'Artikel berhasil dihapus.');
    }

    public function preview(Article $artikel)
    {
        return view('artikel.show', [
            'article' => $artikel,
            'related' => collect(),
        ]);
    }

    protected function validateArticle(Request $request, ?Article $article = null): array
    {
        $uniqueSlug = 'unique:articles,slug' . ($article ? ',' . $article->id : '');

        return $request->validate([
            'title' => 'required|string|max:255',
            'slug' => "required|string|max:255|{$uniqueSlug}",
            'thumbnail' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'content' => 'required|string',
            'author' => 'nullable|string|max:255',
            'date' => 'required|date',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'status' => 'required|in:draft,published',
        ]);
    }
}
