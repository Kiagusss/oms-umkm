<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'user_id',
        'source_type',
        'source_url',
        'status',
        'total_found',
        'imported_count',
        'skipped_count',
        'failed_count',
        'summary',
        'error_message',
    ];

    protected $casts = [
        'summary' => 'array',
        'total_found' => 'integer',
        'imported_count' => 'integer',
        'skipped_count' => 'integer',
        'failed_count' => 'integer',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CatalogImportItem::class);
    }
}
