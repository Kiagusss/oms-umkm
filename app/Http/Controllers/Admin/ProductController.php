<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
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

        $product = Product::create($validated);
        $product->images = $request->input('images', []);
        $product->save();

        return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Product $produk)
    {
        return view('admin.produk.show', compact('produk'));
    }

    public function edit(Product $produk)
    {
        $categories = Category::orderBy('ord')->get();

        return view('admin.produk.edit', compact('produk', 'categories'));
    }

    public function update(Request $request, Product $produk)
    {
        $validated = $this->validateProduct($request, $produk);

        $produk->update($validated);
        $produk->images = $request->input('images', []);
        $produk->save();

        return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $produk)
    {
        $produk->delete();

        return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil dihapus.');
    }

    protected function validateProduct(Request $request, ?Product $product = null): array
    {
        $uniqueSlug = 'unique:products,slug' . ($product ? ',' . $product->id : '');

        return $request->validate([
            'name' => 'required|string|max:255',
            'slug' => "required|string|max:255|{$uniqueSlug}",
            'category_id' => 'nullable|exists:categories,id',
            'price' => 'required|integer|min:0',
            'price_strikethrough' => 'nullable|integer|min:0',
            'short_description' => 'required|string',
            'description' => 'nullable|string',
            'composition' => 'nullable|string',
            'stock' => 'required|integer|min:0',
            'weight' => 'nullable|integer|min:0',
            'thumbnail' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'is_best_seller' => 'sometimes|boolean',
            'is_featured' => 'sometimes|boolean',
            'ord' => 'nullable|integer',
            'images' => 'nullable|array',
        ]);
    }
}
