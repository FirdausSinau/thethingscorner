<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'product_id',
    'sku',
    'size',
    'color',
    'price',
    'stock_on_hand',
    'stock_reserved',
])]
class ProductVariant extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock_on_hand' => 'integer',
            'stock_reserved' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected function stockAvailable(): Attribute
    {
        return Attribute::get(fn () => $this->stock_on_hand - $this->stock_reserved);
    }

    protected function isInStock(): Attribute
    {
        return Attribute::get(fn () => $this->stock_available > 0);
    }

    #[Scope]
    protected function available(Builder $query): Builder
    {
        return $query->whereColumn('stock_on_hand', '>', 'stock_reserved');
    }
}
