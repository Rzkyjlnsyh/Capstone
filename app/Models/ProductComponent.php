<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'component_id',
        'quantity',
        'unit_override',
        'unit_cost_override',
    ];

    protected $casts = [
        'quantity' => 'float',
        'unit_cost_override' => 'float',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class);
    }
}
