<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatalogImportItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'catalog_import_id',
        'external_id',
        'sku',
        'name',
        'category_name',
        'price',
        'action_taken',
        'status',
        'error_message',
        'raw_data',
    ];

    protected $casts = [
        'price' => 'integer',
        'raw_data' => 'array',
    ];

    public function catalogImport(): BelongsTo
    {
        return $this->belongsTo(CatalogImport::class, 'catalog_import_id');
    }
}
