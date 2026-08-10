<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UploadImageController extends Controller
{
    /**
     * Upload gambar untuk admin (produk, banner, artikel, galeri, testimoni).
     * POST /api/upload-image  (multipart: image)
     * → { url: "/storage/uploads/xxx.jpg" }
     */
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:4096',
        ]);

        $file = $request->file('image');
        $name = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('uploads', $name, 'public');

        return response()->json(['url' => '/storage/' . $path]);
    }
}
