<?php

namespace App\Models;

use App\Enums\OrderChannel;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'order_code',
    'user_id',
    'channel',
    'status',
    'payment_method',
    'subtotal',
    'shipping_cost',
    'total',
    'recipient',
    'phone',
    'address',
    'city',
    'postal_code',
    'paid_at',
    'completed_at',
])]
class Order extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'channel' => OrderChannel::class,
            'status' => OrderStatus::class,
            'payment_method' => PaymentMethod::class,
            'subtotal' => 'integer',
            'shipping_cost' => 'integer',
            'total' => 'integer',
            'paid_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(StockReservation::class);
    }

    /**
     * Waktu kedaluwarsa reservasi paling awal pada order ini.
     */
    protected function reservationExpiresAt(): Attribute
    {
        return Attribute::get(fn () => $this->reservations->min('expires_at'));
    }
}
