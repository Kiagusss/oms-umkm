<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class StockTransfer extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_REQUESTED = 'requested';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_IN_TRANSIT = 'in_transit';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'transfer_number',
        'from_branch_id',
        'to_branch_id',
        'status',
        'date',
        'shipped_at',
        'received_at',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'shipped_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Ship items: deducts stock from source branch and moves status to in_transit.
     */
    public function ship(?int $userId = null): void
    {
        if (in_array($this->status, [self::STATUS_IN_TRANSIT, self::STATUS_RECEIVED, self::STATUS_CANCELLED])) {
            throw new \DomainException("Transfer cannot be shipped from status: {$this->status}");
        }

        DB::transaction(function () use ($userId) {
            foreach ($this->items as $item) {
                if ($item->inventory_item_id) {
                    $inv = BranchInventory::firstOrCreate(
                        ['branch_id' => $this->from_branch_id, 'inventory_item_id' => $item->inventory_item_id],
                        ['quantity' => 0]
                    );

                    if ((float) $inv->quantity < (float) $item->quantity) {
                        throw new \DomainException("Stok di cabang asal tidak mencukupi untuk transfer item {$item->inventoryItem?->name}");
                    }

                    $newBal = (float) $inv->quantity - (float) $item->quantity;
                    $inv->update(['quantity' => $newBal]);

                    StockMovement::create([
                        'branch_id' => $this->from_branch_id,
                        'inventory_item_id' => $item->inventory_item_id,
                        'type' => 'transfer_out',
                        'quantity' => -$item->quantity,
                        'balance_after' => $newBal,
                        'reference_type' => self::class,
                        'reference_id' => $this->id,
                        'user_id' => $userId,
                        'notes' => 'Transfer keluar #' . $this->transfer_number . ' ke ' . $this->toBranch?->name,
                    ]);
                } elseif ($item->product_variant_id || $item->product_id) {
                    $inv = BranchInventory::firstOrCreate(
                        [
                            'branch_id' => $this->from_branch_id,
                            'product_id' => $item->product_id,
                            'product_variant_id' => $item->product_variant_id,
                        ],
                        ['quantity' => 0]
                    );

                    if ((float) $inv->quantity < (float) $item->quantity) {
                        throw new \DomainException("Stok produk di cabang asal tidak mencukupi untuk transfer");
                    }

                    $newBal = (float) $inv->quantity - (float) $item->quantity;
                    $inv->update(['quantity' => $newBal]);

                    StockMovement::create([
                        'branch_id' => $this->from_branch_id,
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'type' => 'transfer_out',
                        'quantity' => -$item->quantity,
                        'balance_after' => $newBal,
                        'reference_type' => self::class,
                        'reference_id' => $this->id,
                        'user_id' => $userId,
                        'notes' => 'Transfer keluar #' . $this->transfer_number . ' ke ' . $this->toBranch?->name,
                    ]);
                }
            }

            $this->update([
                'status' => self::STATUS_IN_TRANSIT,
                'shipped_at' => now(),
            ]);
        });
    }

    /**
     * Receive items: adds stock to destination branch and marks transfer received.
     */
    public function receive(?int $userId = null): void
    {
        if ($this->status !== self::STATUS_IN_TRANSIT) {
            throw new \DomainException("Transfer must be in_transit before it can be received");
        }

        DB::transaction(function () use ($userId) {
            foreach ($this->items as $item) {
                if ($item->inventory_item_id) {
                    $inv = BranchInventory::firstOrCreate(
                        ['branch_id' => $this->to_branch_id, 'inventory_item_id' => $item->inventory_item_id],
                        ['quantity' => 0]
                    );

                    $newBal = (float) $inv->quantity + (float) $item->quantity;
                    $inv->update(['quantity' => $newBal]);

                    StockMovement::create([
                        'branch_id' => $this->to_branch_id,
                        'inventory_item_id' => $item->inventory_item_id,
                        'type' => 'transfer_in',
                        'quantity' => $item->quantity,
                        'balance_after' => $newBal,
                        'reference_type' => self::class,
                        'reference_id' => $this->id,
                        'user_id' => $userId,
                        'notes' => 'Transfer masuk #' . $this->transfer_number . ' dari ' . $this->fromBranch?->name,
                    ]);
                } elseif ($item->product_variant_id || $item->product_id) {
                    $inv = BranchInventory::firstOrCreate(
                        [
                            'branch_id' => $this->to_branch_id,
                            'product_id' => $item->product_id,
                            'product_variant_id' => $item->product_variant_id,
                        ],
                        ['quantity' => 0]
                    );

                    $newBal = (float) $inv->quantity + (float) $item->quantity;
                    $inv->update(['quantity' => $newBal]);

                    StockMovement::create([
                        'branch_id' => $this->to_branch_id,
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'type' => 'transfer_in',
                        'quantity' => $item->quantity,
                        'balance_after' => $newBal,
                        'reference_type' => self::class,
                        'reference_id' => $this->id,
                        'user_id' => $userId,
                        'notes' => 'Transfer masuk #' . $this->transfer_number . ' dari ' . $this->fromBranch?->name,
                    ]);
                }
            }

            $this->update([
                'status' => self::STATUS_RECEIVED,
                'received_at' => now(),
            ]);
        });
    }
}
