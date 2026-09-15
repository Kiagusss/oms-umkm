<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%");
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $products = $query->orderBy('ord')->paginate(15);

        return view('admin.produk.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::orderBy('ord')->get();

        return view('admin.produk.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateProduct($request);

        // Gambar utama (opsional saat create)
        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $this->storeImage($request->file('thumbnail'), 'products');
        }

        // Gambar galeri (opsional saat create)
        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $file) {
                $images[] = $this->storeImage($file, 'products');
            }
            $validated['images'] = $images;
        }

        $product = Product::create($validated);

        if ($request->has('variants') && is_array($request->variants)) {
            foreach ($request->variants as $v) {
                if (empty($v['name'])) continue;
                $product->variants()->create([
                    'name' => $v['name'],
                    'sku' => $v['sku'] ?? null,
                    'price' => (int) ($v['price'] ?? $product->price),
                    'cost_price' => (float) ($v['cost_price'] ?? $product->cost_price ?? 0),
                    'stock' => (int) ($v['stock'] ?? 0),
                    'is_active' => isset($v['is_active']) ? (bool) $v['is_active'] : true,
                ]);
            }
            $product->update(['has_variants' => true]);
        }

        return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Product $produk)
    {
        $produk->load(['variants', 'recipe.items.inventoryItem']);
        return view('admin.produk.show', compact('produk'));
    }

    public function edit(Product $produk)
    {
        $categories = Category::orderBy('ord')->get();
        $produk->load('variants');

        return view('admin.produk.edit', compact('produk', 'categories'));
    }

    public function update(Request $request, Product $produk)
    {
        $validated = $this->validateProduct($request, $produk);

        // Simpan state lama untuk hapus file SETELAH sukses update
        $oldThumbnail = $produk->thumbnail;
        $oldImages = $produk->images ?? [];

        // === Gambar utama ===
        if ($request->boolean('remove_thumbnail')) {
            $produk->thumbnail = null;
        }
        if ($request->hasFile('thumbnail')) {
            $produk->thumbnail = $this->storeImage($request->file('thumbnail'), 'products');
        }

        // === Gambar galeri ===
        $currentImages = $oldImages;

        // Hapus item yang dicentang
        $removedIndexes = [];
        if ($request->has('remove_images')) {
            foreach ((array) $request->input('remove_images') as $idx) {
                if (isset($currentImages[$idx])) {
                    unset($currentImages[$idx]);
                    $removedIndexes[] = (int) $idx;
                }
            }
            $currentImages = array_values($currentImages); // reindex
        }

        // Tambah file baru
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $currentImages[] = $this->storeImage($file, 'products');
            }
        }

        $produk->update($validated);
        $produk->images = $currentImages;
        $produk->save();

        // Sync variants
        if ($request->has('variants') && is_array($request->variants)) {
            $existingIds = [];
            foreach ($request->variants as $v) {
                if (empty($v['name'])) continue;
                $var = $produk->variants()->updateOrCreate(
                    ['id' => $v['id'] ?? null],
                    [
                        'name' => $v['name'],
                        'sku' => $v['sku'] ?? null,
                        'price' => (int) ($v['price'] ?? $produk->price),
                        'cost_price' => (float) ($v['cost_price'] ?? $produk->cost_price ?? 0),
                        'stock' => (int) ($v['stock'] ?? 0),
                        'is_active' => isset($v['is_active']) ? (bool) $v['is_active'] : true,
                    ]
                );
                $existingIds[] = $var->id;
            }
            if (!empty($existingIds)) {
                $produk->variants()->whereNotIn('id', $existingIds)->delete();
                $produk->update(['has_variants' => true]);
            }
        }

        // Bersihkan file lama dari storage (best-effort)
        if ($request->hasFile('thumbnail') || $request->boolean('remove_thumbnail')) {
            $this->deleteStorageFile($oldThumbnail);
        }
        foreach ($removedIndexes as $idx) {
            if (isset($oldImages[$idx])) {
                $this->deleteStorageFile($oldImages[$idx]);
            }
        }

        return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $produk)
    {
        // Bersihkan file sebelum hapus record
        if ($produk->thumbnail) {
            $this->deleteStorageFile($produk->thumbnail);
        }
        foreach (($produk->images ?? []) as $img) {
            $this->deleteStorageFile($img);
        }

        $produk->delete();

        return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil dihapus.');
    }

    protected function validateProduct(Request $request, ?Product $product = null): array
    {
        $uniqueSlug = 'unique:products,slug' . ($product ? ',' . $product->id : '');

        // Validasi SEMUA field (termasuk file & checkbox UI)
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => "required|string|max:255|{$uniqueSlug}",
            'category_id' => 'nullable|exists:categories,id',
            'price' => 'required|integer|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'price_strikethrough' => 'nullable|integer|min:0',
            'short_description' => 'required|string',
            'description' => 'nullable|string',
            'composition' => 'nullable|string',
            'stock' => 'required|integer|min:0',
            'weight' => 'nullable|integer|min:0',
            'has_variants' => 'sometimes|boolean',
            'sku' => 'nullable|string|max:50',
            'barcode' => 'nullable|string|max:50',
            // File upload — JPEG (.jpg/.jpeg), PNG, WebP
            // Pakai 'image' + 'mimes' + 'mimetypes' supaya MIME sniffed dari isi file,
            // bukan dari ekstensi nama. Ini mengatasi JPEG dari kamera/HP yang sering
            // mengirim MIME image/jpeg tapi extension .jpg, atau HEIC yang di-rename .jpg.
            // max:2048 KB (2 MB) — di bawah default PHP upload_max_filesize=2M. Naikkan
            // upload_max_filesize di php.ini kalau mau lebih besar.
            'thumbnail' => 'nullable|file|image|mimes:jpeg,jpg,png,webp|mimetypes:image/jpeg,image/png,image/webp|max:2048|dimensions:max_width=4096,max_height=4096',
            'images' => 'nullable|array|max:8',
            'images.*' => 'file|image|mimes:jpeg,jpg,png,webp|mimetypes:image/jpeg,image/png,image/webp|max:2048|dimensions:max_width=4096,max_height=4096',
            'status' => 'required|in:active,inactive',
            'is_best_seller' => 'sometimes|boolean',
            'is_featured' => 'sometimes|boolean',
            'ord' => 'nullable|integer',
            // Checkbox kontrol UI (tidak masuk mass-assignment)
            'remove_thumbnail' => 'sometimes|boolean',
            'remove_images' => 'sometimes|array',
            'remove_images.*' => 'integer',
        ], [
            'thumbnail.required' => 'Gambar utama wajib dipilih.',
            'thumbnail.file'     => 'Gambar utama tidak terbaca sebagai file.',
            'thumbnail.uploaded' => 'Gambar utama gagal diunggah. Pastikan ukuran file ≤ 2 MB dan server mengizinkan upload.',
            'thumbnail.image'    => 'Gambar utama bukan gambar yang valid.',
            'thumbnail.mimes'    => 'Gambar utama harus berformat JPG, JPEG, PNG, atau WebP.',
            'thumbnail.mimetypes'=> 'Tipe isi gambar utama tidak didukung (JPG, JPEG, PNG, atau WebP saja).',
            'thumbnail.max'      => 'Ukuran gambar utama maksimal 2 MB.',
            'thumbnail.dimensions'=> 'Dimensi gambar utama terlalu besar (maks 4096×4096).',
            'images.max'         => 'Maksimal 8 gambar galeri.',
            'images.*.file'      => 'Salah satu gambar galeri tidak terbaca sebagai file.',
            'images.*.uploaded'  => 'Salah satu gambar galeri gagal diunggah. Pastikan setiap file ≤ 2 MB.',
            'images.*.image'     => 'Salah satu gambar galeri bukan gambar yang valid.',
            'images.*.mimes'     => 'Setiap gambar harus berformat JPG, JPEG, PNG, atau WebP.',
            'images.*.mimetypes' => 'Tipe isi salah satu gambar tidak didukung (JPG, JPEG, PNG, atau WebP saja).',
            'images.*.max'       => 'Setiap gambar maksimal 2 MB.',
            'images.*.dimensions'=> 'Dimensi salah satu gambar terlalu besar (maks 4096×4096).',
        ]);

        // BUANG field file & checkbox UI dari data yang aman untuk mass-assignment.
        // Field gambar di-handle terpisah oleh controller; thumbnail/images UploadedFile
        // tidak boleh masuk Product::create()/update() (akan gagal JSON-encode).
        unset(
            $validated['thumbnail'],
            $validated['images'],
            $validated['remove_thumbnail'],
            $validated['remove_images'],
        );

        return $validated;
    }

    /**
     * Simpan satu gambar ke disk public dengan nama random (Str::ulid) dan kembalikan URL publiknya.
     *
     * Whitelist MIME + validasi getimagesize() — konsisten dengan hardening
     * yang sudah ada di UploadImageController.
     */
    protected function storeImage(UploadedFile $file, string $folder): string
    {
        // Re-validasi: pastikan isi file benar-benar gambar
        $imageInfo = @getimagesize($file->getRealPath());
        if ($imageInfo === false) {
            abort(422, 'File bukan gambar yang valid.');
        }

        $ext = match ($imageInfo[2]) {
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG  => 'png',
            IMAGETYPE_WEBP => 'webp',
            IMAGETYPE_GIF  => 'gif',
            default        => null,
        };

        if ($ext === null) {
            abort(422, 'Tipe gambar tidak didukung.');
        }

        $relativeDir  = $folder . '/' . now()->format('Y/m/d');
        $relativePath = $relativeDir . '/' . Str::ulid() . '.' . $ext;

        $file->storeAs($relativeDir, basename($relativePath), 'public');

        return '/storage/' . $relativePath;
    }

    /**
     * Hapus file dari disk public berdasarkan URL publik.
     * Mis. '/storage/products/2026/08/15/xxx.jpg' → hapus 'products/2026/08/15/xxx.jpg'.
     * Hanya folder yang kita kenal yang boleh dihapus (anti path-traversal).
     */
    protected function deleteStorageFile(?string $publicPath): void
    {
        if (! $publicPath || ! str_starts_with($publicPath, '/storage/')) {
            return;
        }
        $path = Str::after($publicPath, '/storage/');

        $allowed = '#^(products|banners|articles|galleries|testimonials|avatars)/#';
        if (! preg_match($allowed, $path)) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
