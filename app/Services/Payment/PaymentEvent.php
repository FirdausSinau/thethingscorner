<?php

namespace App\Services\Payment;

use App\Enums\PaymentStatus;

final readonly class PaymentEvent
{
    /**
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public string $provider,
        public string $externalId,
        public PaymentStatus $status,
        public int $amount,
        public array $raw = [],
    ) {}
}
