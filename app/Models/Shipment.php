<?php

namespace App\Models;

use App\Enums\ShipmentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'provider',
    'courier',
    'service',
    'provider_reference',
    'tracking_number',
    'cost',
    'weight_grams',
    'status',
    'shipped_at',
    'delivered_at',
    'notes',
])]
class Shipment extends Model
{
    /**
     * Mirror database defaults so a newly created model can be checked
     * before it is refreshed from the database.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'provider' => 'fake',
        'status' => 'PENDING',
        'cost' => 0,
        'weight_grams' => 0,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ShipmentStatus::class,
            'cost' => 'integer',
            'weight_grams' => 'integer',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isFinal(): bool
    {
        return $this->status->isFinal();
    }

    /**
     * Shipment yang masih berjalan dan boleh dipakai untuk auto-selesai.
     */
    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->whereIn('status', [
            ShipmentStatus::Pending->value,
            ShipmentStatus::Pickup->value,
            ShipmentStatus::InTransit->value,
        ]);
    }
}
