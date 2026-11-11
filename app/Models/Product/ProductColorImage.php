<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable(['product_color_id', 'path', 'sort_order'])]
class ProductColorImage extends Model
{
    public function color() { return $this->belongsTo(ProductColor::class, 'product_color_id'); }

    public function getUrlAttribute(): ?string
    {
        if (! $this->path || ! Storage::disk('public')->exists($this->path)) {
            return null;
        }

        return Storage::disk('public')->url($this->path);
    }
}

