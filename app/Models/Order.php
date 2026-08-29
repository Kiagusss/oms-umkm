<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['name', 'whatsapp', 'address', 'shipping_courier', 'shipping_cost', 'products', 'notes', 'date', 'status', 'payment_method', 'cash_received', 'change_amount', 'voucher_id', 'discount'];

    protected $casts = [
        'products' => 'array',
        'date' => 'datetime',
        'discount' => 'integer',
    ];

    public const STATUSES = ['pending', 'processing', 'completed', 'cancelled'];

    public function total(): int
    {
        $items = is_array($this->products) ? $this->products : json_decode((string) $this->products, true) ?? [];
        $total = 0;
        foreach ((array) $items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    /** Total akhir: barang - diskon + ongkir. */
    public function grandTotal(): int
    {
        return max(0, $this->total() - (int) ($this->discount ?? 0)) + (int) ($this->shipping_cost ?? 0);
    }
}
