<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UploadImageController extends Controller
{
    /**
     * Upload gambar untuk admin (produk, banner, artikel, galeri, testimoni).
     * POST /api/upload-image  (multipart: image)
     * → { url: "/storage/uploads/xxx.webp" }
     *
     * Hardening (security audit 2026-08-15):
     * - Whitelist MIME types (bukan blacklist)
     * - Whitelist ekstensi
     * - Max dimension (anti pixel-flood attack)
     * - Generate nama file random (anti path traversal)
     * - Validasi via getimagesize() (cek benar-benar image, bukan file disamarkan)
     */
    public function store(Request $request)
    {
        // Whitelist — JANGAN pakai blacklist
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $allowedExts  = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        $request->validate([
            'image' => [
                'required',
                'file',
                'max:5120',              // 5 MB
                Rule::dimensions()->maxWidth(4096)->maxHeight(4096),
                'mimetypes:' . implode(',', $allowedMimes),
            ],
        ], [
            'image.mimetypes' => 'File harus gambar (JPG, PNG, WebP, atau GIF).',
            'image.dimensions' => 'Ukuran gambar maksimal 4096x4096 piksel.',
            'image.max'        => 'Ukuran file maksimal 5 MB.',
        ]);

        $file = $request->file('image');

        // Verifikasi isi file benar-benar image (bukan rename .exe jadi .jpg)
        $imageInfo = @getimagesize($file->getRealPath());
        if ($imageInfo === false) {
            return response()->json(['error' => 'File bukan gambar yang valid.'], 422);
        }

        // Normalisasi ekstensi dari MIME (lebih aman daripada getClientOriginalExtension)
        $ext = $this->extFromMime($imageInfo[2]);
        if (! in_array($ext, $allowedExts, true)) {
            return response()->json(['error' => 'Tipe gambar tidak didukung.'], 422);
        }

        // Generate nama random (anti path traversal & anti cache-busting predictable)
        $name = now()->format('Y/m/d') . '/' . Str::ulid() . '.' . $ext;

        $path = $file->storeAs('uploads', $name, 'public');

        return response()->json(['url' => '/storage/' . $path]);
    }

    /**
     * Map IMAGE_* constant ke ekstensi file.
     *
     * @param int $imageType IMAGETYPE_* constant dari getimagesize()[2]
     */
    private function extFromMime(int $imageType): ?string
    {
        return match ($imageType) {
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG  => 'png',
            IMAGETYPE_WEBP => 'webp',
            IMAGETYPE_GIF  => 'gif',
            default        => null,
        };
    }
}
