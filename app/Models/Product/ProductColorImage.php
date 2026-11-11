<?php

namespace App\Models\Product;

use App\Support\ProductImageDisk;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['product_color_id', 'path', 'sort_order'])]
class ProductColorImage extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(ProductColor::class, 'product_color_id');
    }

    protected function url(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (! $this->path) {
                return null;
            }

            if (ProductImageDisk::name() === 'public' && ! ProductImageDisk::disk()->exists($this->path)) {
                return null;
            }

            return ProductImageDisk::disk()->url($this->path);
        });
    }
}
