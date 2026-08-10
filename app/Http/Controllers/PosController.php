<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Services\TransactionService;
use App\Http\Requests\PosCheckoutRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function index()
    {
        return view('admin.pos');
    }

    public function products(Request $request): JsonResponse
    {
        $products = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->get();

        $categories = Category::query()
            ->whereHas('products', fn($q) => $q->where('is_active', true))
            ->get();

        return response()->json([
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    public function checkout(PosCheckoutRequest $request, TransactionService $service): JsonResponse
    {
        try {
            $order = $service->createOrder($request->validated());
            return response()->json([
                'success' => true,
                'order_id' => $order->order_number,
                'order' => $order->load('items'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}