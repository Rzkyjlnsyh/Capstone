<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HpeResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'exchange_rate_id',
        'calculated_by',
        'margin_percent',
        'total_cost_idr',
        'total_with_margin',
        'status',
        'component_breakdown',
        'warnings',
        'calculated_at',
    ];

    protected $casts = [
        'component_breakdown' => 'array',
        'warnings' => 'array',
        'calculated_at' => 'datetime',
        'margin_percent' => 'float',
        'total_cost_idr' => 'float',
        'total_with_margin' => 'float',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function exchangeRate(): BelongsTo
    {
        return $this->belongsTo(ExchangeRate::class);
    }

    public function calculatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'calculated_by');
    }
}
