<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'category',
        'status',
        'metadata',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function components(): BelongsToMany
    {
        return $this->belongsToMany(Component::class, 'product_components')
            ->withPivot(['id', 'quantity', 'unit_override', 'unit_cost_override'])
            ->withTimestamps();
    }

    public function productComponents(): HasMany
    {
        return $this->hasMany(ProductComponent::class);
    }

    public function purchaseHistories(): HasMany
    {
        return $this->hasMany(PurchaseHistory::class);
    }

    public function hpeResults(): HasMany
    {
        return $this->hasMany(HpeResult::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
