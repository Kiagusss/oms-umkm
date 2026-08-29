<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    // ponytail: tax rate hardcoded 0%; move to settings table when PPN applies
    private const TAX_RATE = 0;

    public function __construct(private HappyHourService $happyHour)
    {
    }

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

            // Happy hour: harga efektif dihitung server-side saat request.
            $discountPercent = $this->happyHour->discountPercent();
            $effectivePrice  = $this->happyHour->discountedPrice((int) $product->price, $discountPercent);

            $lineTotal = $effectivePrice * $line['quantity'];
            $subtotal += $lineTotal;

            $lineItems[] = [
                'productId'   => $product->id,
                'productName' => $product->name,
                'price'       => $effectivePrice,
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

        $voucher = null;
        $discount = 0;

        if (!empty($validated['voucher_code'])) {
            $voucher = Voucher::where('code', $validated['voucher_code'])->first();
            if (!$voucher) {
                throw new \InvalidArgumentException('Kode voucher tidak valid');
            }
            $discount = $this->voucherDiscount($voucher, $calc['total']);
            if ($discount === null) {
                throw new \InvalidArgumentException('Voucher tidak berlaku');
            }
        }

        $grandTotal = $calc['total'] - $discount;

        return DB::transaction(function () use ($validated, $calc, $voucher, $discount, $grandTotal) {
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
                $changeAmount = max(0, $cashReceived - $grandTotal);
            }

            // QRIS = simulasi: order menunggu "pembayaran" (settle manual admin),
            // metode lain langsung completed seperti biasa.
            $status = $paymentMethod === 'QRIS' ? 'pending' : 'completed';

            if ($voucher) {
                $voucher->increment('used_count');
            }

            return Order::create([
                'name'             => $validated['customer_name'],
                'whatsapp'         => $validated['customer_whatsapp'] ?? '',
                'address'          => $validated['address'] ?? null,
                'shipping_courier' => $validated['shipping_courier'] ?? null,
                'shipping_cost'    => (int) ($validated['shipping_cost'] ?? 0),
                'products'         => json_encode($calc['lineItems'], JSON_UNESCAPED_UNICODE),
                'notes'            => $validated['notes'] ?? null,
                'date'             => now()->toDateString(),
                'status'           => $status,
                'payment_method'   => $paymentMethod,
                'cash_received'    => $cashReceived,
                'change_amount'    => $changeAmount,
                'voucher_id'       => $voucher?->id,
                'discount'         => $discount,
            ]);
        });
    }

    /**
     * Calculate voucher discount, or null if voucher is not usable.
     */
    private function voucherDiscount(Voucher $voucher, int $total): ?int
    {
        $now = now();

        if (!$voucher->is_active) return null;
        if ($voucher->valid_from && $now->lt($voucher->valid_from)) return null;
        if ($voucher->valid_until && $now->gt($voucher->valid_until)) return null;
        if ($voucher->max_uses !== null && $voucher->used_count >= $voucher->max_uses) return null;
        if ($voucher->min_order !== null && $total < $voucher->min_order) return null;

        if ($voucher->type === 'percentage') {
            $discount = (int) round($total * $voucher->value / 100);
        } else {
            $discount = (int) $voucher->value;
        }

        return min($discount, $total);
    }
}