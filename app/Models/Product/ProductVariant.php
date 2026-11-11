<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['product_color_id', 'size', 'sku', 'stock', 'stock_reserved', 'price'])]
class ProductVariant extends Model
{
    protected function casts(): array
    {
        return [
            'stock' => 'integer',
            'stock_reserved' => 'integer',
            'price' => 'integer',
        ];
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(ProductColor::class, 'product_color_id');
    }

    protected function stockAvailable(): Attribute
    {
        return Attribute::get(fn () => $this->stock - $this->stock_reserved);
    }
}
