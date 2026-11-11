<?php

namespace App\Models;

use App\Models\Product\ProductVariant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'variant_id', 'qty'])]
class CartItem extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'qty' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    protected function unitPrice(): Attribute
    {
        return Attribute::get(fn () => $this->variant->effective_price);
    }

    protected function lineTotal(): Attribute
    {
        return Attribute::get(fn () => $this->unit_price * $this->qty);
    }
}
