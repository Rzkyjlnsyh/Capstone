<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExchangeRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'rate_date',
        'base_currency',
        'quote_currency',
        'rate_value',
        'source',
        'fetched_at',
        'raw_payload',
    ];

    protected $casts = [
        'rate_date' => 'date',
        'fetched_at' => 'datetime',
        'raw_payload' => 'array',
        'rate_value' => 'float',
    ];

    public function purchaseHistories(): HasMany
    {
        return $this->hasMany(PurchaseHistory::class);
    }

    public function hpeResults(): HasMany
    {
        return $this->hasMany(HpeResult::class);
    }
}
