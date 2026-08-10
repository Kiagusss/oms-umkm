<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;

class PublicCategoryController extends Controller
{
    public function show(string $slug)
    {
        $category = Category::where('slug', $slug)->where('status', 'active')->firstOrFail();
        $products = Product::where('category_id', $category->id)
            ->where('status', 'active')
            ->orderBy('ord')
            ->get();

        return view('kategori.show', [
            'category' => $category,
            'products' => $products,
        ]);
    }
}
