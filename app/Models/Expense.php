<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    public const CATEGORIES = [
        'Gaji',
        'Sewa',
        'Listrik',
        'Air',
        'Gas',
        'Packaging',
        'Transportasi',
        'Pemasaran',
        'Pemeliharaan',
        'Perlengkapan',
        'Lainnya',
    ];

    protected $fillable = [
        'branch_id',
        'expense_number',
        'category',
        'amount',
        'date',
        'payment_method',
        'description',
        'receipt_image',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'integer',
        'date' => 'date',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
