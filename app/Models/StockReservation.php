<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use App\Models\Product\ProductVariant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['order_id', 'variant_id', 'qty', 'expires_at', 'status'])]
class StockReservation extends Model
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
            'expires_at' => 'datetime',
            'status' => ReservationStatus::class,
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('status', ReservationStatus::Active->value);
    }
}
