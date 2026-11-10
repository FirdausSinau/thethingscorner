<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;;

#[Fillable(['product_color_id', 'path', 'sort_order'])]
class ProductColorImage extends Model
{
    public function color() { return $this->belongsTo(ProductColor::class, 'product_color_id'); }

    public function getUrlAttribute(): string
    {
        return Storage::disk('s3')->url($this->path);
    }
}
