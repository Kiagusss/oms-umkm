<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use BelongsToStore;

    protected $fillable = [
        'store_id',
        'branch_id',
        'name',
        'whatsapp',
        'address',
        'shipping_courier',
        'shipping_cost',
        'products',
        'notes',
        'date',
        'status',
        'payment_method',
        'cash_received',
        'change_amount',
        'voucher_id',
        'discount',
        'total_cogs',
        'gross_profit',
    ];

    protected $casts = [
        'products' => 'array',
        'date' => 'datetime',
        'shipping_cost' => 'integer',
        'cash_received' => 'integer',
        'change_amount' => 'integer',
        'discount' => 'integer',
        'total_cogs' => 'integer',
        'gross_profit' => 'integer',
    ];

    public const STATUSES = ['pending', 'processing', 'completed', 'cancelled'];

    public function getCogsAttribute(): int
    {
        return (int) ($this->total_cogs ?? 0);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function total(): int
    {
        $items = is_array($this->products) ? $this->products : json_decode((string) $this->products, true) ?? [];
        $total = 0;
        foreach ((array) $items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    public function netSales(): int
    {
        return max(0, $this->total() - (int) ($this->discount ?? 0));
    }

    /** Total akhir: barang - diskon + ongkir. */
    public function grandTotal(): int
    {
        return $this->netSales() + (int) ($this->shipping_cost ?? 0);
    }
}
