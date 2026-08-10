<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryItem;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index()
    {
        $gallery = GalleryItem::orderBy('ord')->paginate(15);

        return view('admin.galeri.index', compact('gallery'));
    }

    public function create()
    {
        return view('admin.galeri.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateGallery($request);

        GalleryItem::create($validated);

        return redirect()->route('admin.galeri.index')->with('success', 'Galeri berhasil ditambahkan.');
    }

    public function show(GalleryItem $galeri)
    {
        return view('admin.galeri.show', compact('galeri'));
    }

    public function edit(GalleryItem $galeri)
    {
        return view('admin.galeri.edit', compact('galeri'));
    }

    public function update(Request $request, GalleryItem $galeri)
    {
        $validated = $this->validateGallery($request);

        $galeri->update($validated);

        return redirect()->route('admin.galeri.index')->with('success', 'Galeri berhasil diperbarui.');
    }

    public function destroy(GalleryItem $galeri)
    {
        $galeri->delete();

        return redirect()->route('admin.galeri.index')->with('success', 'Galeri berhasil dihapus.');
    }

    protected function validateGallery(Request $request): array
    {
        return $request->validate([
            'image' => 'required|string|max:255',
            'caption' => 'nullable|string',
            'ord' => 'nullable|integer',
        ]);
    }
}
