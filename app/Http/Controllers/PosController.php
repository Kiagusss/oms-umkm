<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PosController extends Controller
{
    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_whatsapp' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'payment_method' => ['required', 'string', 'in:Tunai,Transfer'],
            'cash_received' => ['required_if:payment_method,Tunai', 'numeric', 'min:0'],
        ]);

        // Check stock
        foreach ($validated['items'] as $item) {
            $product = Product::find($item['id']);
            if ($product->stock < $item['quantity']) {
                throw ValidationException::withMessages([
                    'items.*.quantity' => 'The selected quantity exceeds available stock.',
                ]);
            }
        }

        // Calculate total
        $total = 0;
        foreach ($validated['items'] as $item) {
            $product = Product::find($item['id']);
            $total += $product->price * $item['quantity'];
        }

        // Create order
        $order = Order::create([
            'name' => $validated['customer_name'],
            'whatsapp' => $validated['customer_whatsapp'],
            'payment_method' => $validated['payment_method'],
            'cash_received' => $validated['cash_received'],
            'status' => 'completed',
            'products' => collect($validated['items'])->map(function ($item) {
                $product = Product::find($item['id']);
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $item['quantity'],
                ];
            })->toArray(),
        ]);

        // Update stock (decrement)
        foreach ($validated['items'] as $item) {
            $product = Product::find($item['id']);
            $product->decrement('stock', $item['quantity']);
        }

        $change = 0;
        if ($validated['payment_method'] === 'Tunai') {
            $change = max(0, $validated['cash_received'] - $total);
        }

        return response()->json([
            'ok' => true,
            'order_id' => $order->id,
            'total' => $total,
            'payment_method' => $validated['payment_method'],
            'cash_received' => $validated['cash_received'],
            'change_amount' => $change,
        ]);
    }
}
