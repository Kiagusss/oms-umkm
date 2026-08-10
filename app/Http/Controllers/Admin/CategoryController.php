<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('ord')->paginate(15);

        return view('admin.kategori.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.kategori.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateCategory($request);

        Category::create($validated);

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function show(Category $kategori)
    {
        return view('admin.kategori.show', compact('kategori'));
    }

    public function edit(Category $kategori)
    {
        return view('admin.kategori.edit', compact('kategori'));
    }

    public function update(Request $request, Category $kategori)
    {
        $validated = $this->validateCategory($request, $kategori);

        $kategori->update($validated);

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $kategori)
    {
        $kategori->delete();

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil dihapus.');
    }

    protected function validateCategory(Request $request, ?Category $category = null): array
    {
        $uniqueSlug = 'unique:categories,slug' . ($category ? ',' . $category->id : '');

        return $request->validate([
            'name' => 'required|string|max:255',
            'slug' => "required|string|max:255|{$uniqueSlug}",
            'icon' => 'nullable|string|max:255',
            'ord' => 'nullable|integer',
            'status' => 'required|in:active,inactive',
        ]);
    }
}
