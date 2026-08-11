<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['name', 'whatsapp', 'products', 'notes', 'date', 'status', 'payment_method', 'cash_received', 'change_amount', 'voucher_id', 'discount'];

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
}
