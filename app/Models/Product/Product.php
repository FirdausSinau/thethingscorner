<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;


#[Fillable([
    'category_id', 'name', 'slug', 'description', 'base_price',
    'is_active', 'is_preorder', 'preorder_close_date', 'estimated_ready_date',
    'weight_grams', 'rating_avg', 'rating_count',
])]
class Product extends Model
{
    protected function casts(): array
    {
        return [
            'base_price'           => 'integer',
            'is_active'            => 'boolean',
            'is_preorder'          => 'boolean',
            'preorder_close_date'  => 'date',
            'estimated_ready_date' => 'date',
            'weight_grams'         => 'integer',
            'rating_avg'           => 'decimal:2',
            'rating_count'         => 'integer',
        ];
    }

    public function category() { return $this->belongsTo(Category::class); }
    public function colors()   { return $this->hasMany(ProductColor::class); }

    public function scopeListed($query)
    {
        return $query->where('is_active', true)
            ->with(['category', 'images', 'colors.images', 'colors.variants']);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->images->first()?->url
            ?? $this->colors->first()?->images->first()?->url;
    }

    public function variants()
    {
        return $this->hasManyThrough(ProductVariant::class, ProductColor::class);
    }
}