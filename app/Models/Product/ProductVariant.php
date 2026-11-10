<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['product_color_id', 'size', 'sku', 'stock', 'price'])]
class ProductVariant extends Model
{
    public function color() { return $this->belongsTo(ProductColor::class, 'product_color_id'); }
}
