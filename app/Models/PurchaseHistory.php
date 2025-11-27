<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'component_id',
        'purchase_date',
        'vendor_name',
        'currency',
        'exchange_rate_id',
        'rate_value_snapshot',
        'quantity',
        'unit_price_original',
        'unit_price_idr',
        'notes',
        'document_reference',
        'metadata',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'metadata' => 'array',
        'rate_value_snapshot' => 'float',
        'unit_price_original' => 'float',
        'unit_price_idr' => 'float',
        'quantity' => 'float',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class);
    }

    public function exchangeRate(): BelongsTo
    {
        return $this->belongsTo(ExchangeRate::class);
    }
}
