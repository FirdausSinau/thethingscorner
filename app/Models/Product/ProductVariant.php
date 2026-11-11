<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function cartItems(): HasMany
    {
        return $this->hasMany(\App\Models\CartItem::class, 'variant_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(\App\Models\OrderItem::class, 'variant_id');
    }

    public function stockReservations(): HasMany
    {
        return $this->hasMany(\App\Models\StockReservation::class, 'variant_id');
    }

    protected function stockAvailable(): Attribute
    {
        return Attribute::get(fn () => $this->stock - $this->stock_reserved);
    }

    protected function effectivePrice(): Attribute
    {
        return Attribute::get(
            fn () => (int) ($this->price ?? $this->color->product->base_price)
        );
    }
}
