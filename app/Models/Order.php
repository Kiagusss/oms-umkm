<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['name', 'whatsapp', 'products', 'notes', 'date', 'status', 'payment_method', 'cash_received', 'change_amount'];

    protected $casts = [
        'products' => 'array',
        'date' => 'datetime',
    ];

    public const STATUSES = ['pending', 'processing', 'completed', 'cancelled'];

    public function total(): int
    {
        $total = 0;
        foreach ((array) $this->products as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }
}
