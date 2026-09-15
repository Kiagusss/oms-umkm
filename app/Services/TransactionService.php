<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Recipe;
use App\Models\StockMovement;
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
     * @param array $items [['id' => int, 'quantity' => int, 'variant_id' => ?int], ...]
     * @param int|null $branchId
     * @return array{subtotal: int, tax: int, total: int, lineItems: array}
     */
    public function calculate(array $items, ?int $branchId = null): array
    {
        $productIds = collect($items)->pluck('id')->unique()->values();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $variantIds = collect($items)->pluck('variant_id')->filter()->unique()->values();
        $variants = $variantIds->isNotEmpty()
            ? ProductVariant::whereIn('id', $variantIds)->get()->keyBy('id')
            : collect();

        $lineItems = [];
        $subtotal = 0;

        foreach ($items as $line) {
            $product = $products->get($line['id']);
            if (!$product) {
                throw new \InvalidArgumentException("Produk ID {$line['id']} tidak ditemukan");
            }

            $variantId = $line['variant_id'] ?? $line['variantId'] ?? null;
            $variant = $variantId ? $variants->get($variantId) : null;

            // Stock check: prioritize branch inventory if branchId is provided
            $availableStock = $variant ? $variant->stock : $product->stock;
            if ($branchId) {
                $branchInv = BranchInventory::where('branch_id', $branchId)
                    ->when($variant, fn($q) => $q->where('product_variant_id', $variant->id))
                    ->when(!$variant, fn($q) => $q->where('product_id', $product->id)->whereNull('product_variant_id'))
                    ->first();

                if ($branchInv !== null) {
                    $availableStock = $branchInv->quantity;
                }
            }

            if ($availableStock < $line['quantity']) {
                $displayName = $variant ? "{$product->name} ({$variant->name})" : $product->name;
                // Preserve exact test expectation format: "Stok {$product->name} tidak mencukupi (tersedia: {$product->stock})"
                $displayLabel = $variant ? $displayName : $product->name;
                throw new \InvalidArgumentException("Stok {$displayLabel} tidak mencukupi (tersedia: {$availableStock})");
            }

            $unitPrice = $variant && $variant->price > 0 ? (int) $variant->price : (int) $product->price;
            $cogsPrice = $variant && $variant->cost_price > 0 ? (int) $variant->cost_price : (int) ($product->cost_price ?? 0);

            // Happy hour: harga efektif dihitung server-side saat request.
            $discountPercent = $this->happyHour->discountPercent();
            $effectivePrice  = $this->happyHour->discountedPrice($unitPrice, $discountPercent);

            $lineTotal = $effectivePrice * $line['quantity'];
            $subtotal += $lineTotal;

            $lineItems[] = [
                'productId'   => $product->id,
                'productName' => $product->name,
                'variantId'   => $variant?->id,
                'variantName' => $variant?->name,
                'price'       => $effectivePrice,
                'cogs'        => $cogsPrice,
                'quantity'    => $line['quantity'],
                'thumbnail'   => $variant?->image ?: $product->thumbnail,
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
        $branchId = $validated['branch_id']
            ?? session('selected_branch_id')
            ?? Branch::where('is_main', true)->value('id')
            ?? Branch::first()?->id;

        $calc = $this->calculate($validated['items'], $branchId);

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

        return DB::transaction(function () use ($validated, $calc, $voucher, $discount, $grandTotal, $branchId) {
            // Decrement stock with lock
            foreach ($validated['items'] as $line) {
                $affected = Product::where('id', $line['id'])
                    ->where('stock', '>=', $line['quantity'])
                    ->decrement('stock', $line['quantity']);

                if ($affected === 0) {
                    throw new \InvalidArgumentException("Stok berubah, silakan coba lagi");
                }

                $variantId = $line['variant_id'] ?? $line['variantId'] ?? null;
                if ($variantId) {
                    $variantAffected = ProductVariant::where('id', $variantId)
                        ->where('stock', '>=', $line['quantity'])
                        ->decrement('stock', $line['quantity']);

                    if ($variantAffected === 0) {
                        throw new \InvalidArgumentException("Stok varian berubah atau tidak mencukupi");
                    }
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

            $totalCogs = 0;
            foreach ($calc['lineItems'] as $item) {
                $totalCogs += ($item['cogs'] ?? 0) * $item['quantity'];
            }
            $netSales = max(0, $calc['subtotal'] - $discount);
            $grossProfit = $netSales - $totalCogs;

            $order = Order::create([
                'branch_id'        => $branchId,
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
                'total_cogs'       => $totalCogs,
                'gross_profit'     => $grossProfit,
            ]);

            // Track branch inventory and ledger movement
            if ($branchId) {
                foreach ($validated['items'] as $line) {
                    $productId = (int) $line['id'];
                    $variantId = isset($line['variant_id']) ? (int) $line['variant_id'] : (isset($line['variantId']) ? (int) $line['variantId'] : null);
                    $qty = (int) $line['quantity'];

                    $branchInv = BranchInventory::where('branch_id', $branchId)
                        ->where('product_id', $productId)
                        ->where('product_variant_id', $variantId)
                        ->first();

                    $balanceAfter = 0;
                    if ($branchInv) {
                        $branchInv->decrement('quantity', $qty);
                        $balanceAfter = (float) $branchInv->fresh()->quantity;
                    }

                    StockMovement::create([
                        'branch_id'          => $branchId,
                        'inventory_item_id'  => null,
                        'product_id'         => $productId,
                        'product_variant_id' => $variantId,
                        'type'               => 'sale',
                        'quantity'           => -$qty,
                        'balance_after'      => $balanceAfter,
                        'reference_type'     => Order::class,
                        'reference_id'       => $order->id,
                        'notes'              => "Penjualan Order #{$order->id}",
                        'user_id'            => auth()->id() ?? session('admin_user_id'),
                    ]);

                    // Deduct recipe/BOM ingredients if recipe exists
                    $recipe = null;
                    if ($variantId) {
                        $recipe = Recipe::where('product_variant_id', $variantId)->with('items')->first();
                    }
                    if (!$recipe) {
                        $recipe = Recipe::where('product_id', $productId)->whereNull('product_variant_id')->with('items')->first();
                    }

                    if ($recipe && $recipe->items->isNotEmpty()) {
                        foreach ($recipe->items as $recipeItem) {
                            if (!$recipeItem->inventory_item_id) continue;
                            $neededQty = (float) $recipeItem->quantity * $qty;
                            $inv = BranchInventory::firstOrCreate(
                                ['branch_id' => $branchId, 'inventory_item_id' => $recipeItem->inventory_item_id],
                                ['quantity' => 0]
                            );
                            $inv->decrement('quantity', $neededQty);
                            $after = (float) $inv->fresh()->quantity;

                            StockMovement::create([
                                'branch_id'         => $branchId,
                                'inventory_item_id' => $recipeItem->inventory_item_id,
                                'product_id'        => null,
                                'product_variant_id'=> null,
                                'type'              => 'usage_order',
                                'quantity'          => -$neededQty,
                                'balance_after'     => $after,
                                'reference_type'    => Order::class,
                                'reference_id'      => $order->id,
                                'notes'             => "Penggunaan bahan resep untuk Order #{$order->id}",
                                'user_id'           => auth()->id() ?? session('admin_user_id'),
                            ]);
                        }
                    }
                }
            }

            return $order;
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