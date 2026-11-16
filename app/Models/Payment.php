<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'provider',
    'method',
    'external_id',
    'amount',
    'status',
    'proof_path',
    'expires_at',
    'paid_at',
    'verified_by',
])]
class Payment extends Model
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
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'method' => PaymentMethod::class,
            'status' => PaymentStatus::class,
            'amount' => 'integer',
            'expires_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isPaid(): bool
    {
        return $this->status === PaymentStatus::Paid;
    }

    #[Scope]
    protected function pending(Builder $query): Builder
    {
        return $query->where('status', PaymentStatus::Pending->value);
    }
}
