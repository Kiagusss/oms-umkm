<?php

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