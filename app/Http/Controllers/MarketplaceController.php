<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Services\Tenant\TenantContext;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    /**
     * Directory of all active stores in the marketplace.
     */
    public function stores(Request $request)
    {
        $query = Store::active()->withCount('products');

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($city = $request->input('city')) {
            $query->where('city', $city);
        }

        $stores = $query->orderBy('is_featured', 'desc')
            ->orderBy('name', 'asc')
            ->paginate(12)
            ->withQueryString();

        $cities = Store::active()->whereNotNull('city')->distinct()->pluck('city');

        return view('marketplace.stores', compact('stores', 'cities'));
    }

    /**
     * Dedicated per-store storefront.
     */
    public function storefront(Request $request, string $slug)
    {
        $store = Store::where('slug', $slug)->firstOrFail();
        TenantContext::setStore($store);

        $query = Product::where('store_id', $store->id)->where('status', 'active');

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->input('category')) {
            $query->where('category_id', $categoryId);
        }

        $sort = $request->input('sort', 'newest');
        match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            default => $query->orderBy('id', 'desc'),
        };

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::where('store_id', $store->id)->get();

        return view('marketplace.storefront', compact('store', 'products', 'categories'));
    }

    /**
     * Product detail inside store context.
     */
    public function storeProduct(Request $request, string $storeSlug, string $productSlug)
    {
        $store = Store::where('slug', $storeSlug)->firstOrFail();
        TenantContext::setStore($store);

        $product = Product::where('store_id', $store->id)
            ->where('slug', $productSlug)
            ->with(['category', 'reviews'])
            ->firstOrFail();

        $relatedProducts = Product::where('store_id', $store->id)
            ->where('id', '!=', $product->id)
            ->where('status', 'active')
            ->take(4)
            ->get();

        return view('marketplace.product-detail', compact('store', 'product', 'relatedProducts'));
    }

    /**
     * Global marketplace product search.
     */
    public function search(Request $request)
    {
        $query = Product::where('status', 'active')->with(['store', 'category']);

        // Scope to active stores only
        $query->whereHas('store', function ($q) {
            $q->where('status', 'active');
        });

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('store', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($storeId = $request->input('store')) {
            $query->where('store_id', $storeId);
        }

        if ($categorySlug = $request->input('category')) {
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        if ($minPrice = $request->input('min_price')) {
            $query->where('price', '>=', (int) $minPrice);
        }

        if ($maxPrice = $request->input('max_price')) {
            $query->where('price', '<=', (int) $maxPrice);
        }

        $sort = $request->input('sort', 'relevance');
        match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'newest' => $query->orderBy('id', 'desc'),
            default => $query->orderBy('is_best_seller', 'desc')->orderBy('ord', 'asc'),
        };

        $products = $query->paginate(16)->withQueryString();
        $stores = Store::active()->get();
        $categories = Category::distinct('name')->get();

        return view('marketplace.search', compact('products', 'stores', 'categories'));
    }
}
