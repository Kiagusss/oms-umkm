<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionService
{
    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $items = $data['items'];
            $productIds = collect($items)->pluck('id')->toArray();
            
            // Fetch products with lock to prevent race condition
            $products = Product::query()
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $orderItems = [];
            $subtotal = 0;

            foreach ($items as $item) {
                $product = $products->get($item['id']);
                if (!$product) {
                    throw new \Exception("Product not found: {$item['id']}");
                }

                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->name}. Available: {$product->stock}");
                }

                $price = (float) $product->price;
                $quantity = (int) $item['quantity'];
                $total = $price * $quantity;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $price,
                    'quantity' => $quantity,
                    'total' => $total,
                ];

                $subtotal += $total;

                // Decrease stock
                $product->stock -= $quantity;
                $product->save();
            }

            $discount = (float) ($data['discount'] ?? 0);
            $tax = (float) ($data['tax'] ?? 0);
            $total = $subtotal - $discount + $tax;

            // Validate payment
            $paymentMethod = $data['payment_method'] ?? 'cash';
            $cashReceived = (float) ($data['cash_received'] ?? 0);

            if ($paymentMethod === 'cash' && $cashReceived < $total) {
                throw new \Exception('Insufficient payment amount.');
            }

            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'customer_name' => $data['customer_name'] ?? 'Guest',
                'customer_whatsapp' => $data['customer_whatsapp'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'payment_method' => $paymentMethod,
                'cash_received' => $paymentMethod === 'cash' ? $cashReceived : null,
                'change' => $paymentMethod === 'cash' ? $cashReceived - $total : null,
                'status' => 'completed',
                'notes' => $data['notes'] ?? null,
                'user_id' => auth()->id(),
            ]);

            foreach ($orderItems as $item) {
                $item['order_id'] = $order->id;
                OrderItem::create($item);
            }

            return $order;
        });
    }

    private function generateOrderNumber(): string
    {
        $prefix = 'INV';
        $date = now()->format('Ymd');
        $last = Order::where('order_number', 'like', "{$prefix}-{$date}-%")
            ->orderBy('order_number', 'desc')
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last->order_number, -4);
            $next = $lastNumber + 1;
        } else {
            $next = 1;
        }

        return "{$prefix}-{$date}-" . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    // ponytail: tax rate hardcoded 0%; move to settings table when PPN applies
    private const TAX_RATE = 0;

    /**
     * @param array $items [['id' => int, 'quantity' => int], ...]
     * @return array{subtotal: int, tax: int, total: int, lineItems: array}
     */
    public function calculate(array $items): array
    {
        $productIds = collect($items)->pluck('id')->unique()->values();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $lineItems = [];
        $subtotal = 0;

        foreach ($items as $line) {
            $product = $products->get($line['id']);
            if (!$product) {
                throw new \InvalidArgumentException("Produk ID {$line['id']} tidak ditemukan");
            }
            if ($product->stock < $line['quantity']) {
                throw new \InvalidArgumentException("Stok {$product->name} tidak mencukupi (tersedia: {$product->stock})");
            }

            $lineTotal = $product->price * $line['quantity'];
            $subtotal += $lineTotal;

            $lineItems[] = [
                'productId'   => $product->id,
                'productName' => $product->name,
                'price'       => $product->price,
                'quantity'    => $line['quantity'],
                'thumbnail'   => $product->thumbnail,
            ];
        }

        $tax = (int) round($subtotal * self::TAX_RATE / 100);

        return [
            'subtotal'  => $subtotal,
            'tax'       => $tax,
            'total'     => $subtotal + $tax,
            'lineItems' => $lineItems,
        ];
    }

    /**
     * Execute checkout inside DB transaction.
     * Returns created Order.
     */
    public function checkout(array $validated): Order
    {
        $calc = $this->calculate($validated['items']);

        return DB::transaction(function () use ($validated, $calc) {
            // Decrement stock with lock
            foreach ($validated['items'] as $line) {
                $affected = Product::where('id', $line['id'])
                    ->where('stock', '>=', $line['quantity'])
                    ->decrement('stock', $line['quantity']);

                if ($affected === 0) {
                    throw new \InvalidArgumentException("Stok berubah, silakan coba lagi");
                }
            }

            $paymentMethod = $validated['payment_method'] ?? 'Tunai';
            $cashReceived  = $validated['cash_received'] ?? null;
            $changeAmount  = null;

            if ($paymentMethod === 'Tunai' && $cashReceived !== null) {
                $changeAmount = max(0, $cashReceived - $calc['total']);
            }

            return Order::create([
                'name'           => $validated['customer_name'],
                'whatsapp'       => $validated['customer_whatsapp'] ?? '',
                'products'       => json_encode($calc['lineItems'], JSON_UNESCAPED_UNICODE),
                'notes'          => $validated['notes'] ?? null,
                'date'           => now()->toDateString(),
                'status'         => 'completed',
                'payment_method' => $paymentMethod,
                'cash_received'  => $cashReceived,
                'change_amount'  => $changeAmount,
            ]);
        });
    }
}