<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Purchase extends Model
{
    protected $fillable = [
        'purchase_number',
        'supplier_id',
        'branch_id',
        'date',
        'total_amount',
        'payment_status',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'total_amount' => 'integer',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function receive(?int $userId = null): void
    {
        if (in_array($this->status, ['received', 'completed'], true)) {
            return;
        }

        DB::transaction(function () use ($userId) {
            foreach ($this->items as $item) {
                $inventoryItem = $item->inventoryItem;
                if (!$inventoryItem) {
                    continue;
                }

                // 1. Update or create branch inventory
                $branchInv = BranchInventory::firstOrCreate(
                    [
                        'branch_id' => $this->branch_id,
                        'inventory_item_id' => $inventoryItem->id,
                    ],
                    [
                        'quantity' => 0,
                        'minimum_stock' => $inventoryItem->minimum_stock,
                    ]
                );

                $existingQty = (float) $branchInv->quantity;
                $existingCost = (int) $inventoryItem->cost_per_unit;
                $incomingQty = (float) $item->quantity;
                $incomingCost = (int) $item->unit_price;

                $newBalance = $existingQty + $incomingQty;
                $branchInv->update(['quantity' => $newBalance]);

                // 2. Record stock movement
                StockMovement::create([
                    'branch_id' => $this->branch_id,
                    'inventory_item_id' => $inventoryItem->id,
                    'type' => 'purchase',
                    'quantity' => $item->quantity,
                    'balance_after' => $newBalance,
                    'reference_type' => self::class,
                    'reference_id' => $this->id,
                    'user_id' => $userId,
                    'notes' => 'Pembelian #' . $this->purchase_number . ' dari ' . ($this->supplier?->name ?? 'Supplier'),
                ]);

                // 3. Update material cost using weighted moving average
                if ($newBalance > 0 && $existingQty > 0) {
                    $weightedCost = (int) round((($existingQty * $existingCost) + ($incomingQty * $incomingCost)) / $newBalance);
                } else {
                    $weightedCost = $incomingCost;
                }
                $inventoryItem->update(['cost_per_unit' => $weightedCost]);

                // 4. Recalculate recipes that use this inventory item (so current product HPP updates)
                $recipesToUpdate = Recipe::whereHas('items', function ($q) use ($inventoryItem) {
                    $q->where('inventory_item_id', $inventoryItem->id);
                })->get();

                foreach ($recipesToUpdate as $recipe) {
                    $recipe->recalculateHpp($userId, 'purchase_recalc');
                }
            }

            $this->update(['status' => 'received']);
        });
    }
}
