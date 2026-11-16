<?php

namespace App\Services\Payment;

use App\Enums\PaymentMethod;
use DateTimeInterface;

final readonly class PaymentInstruction
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $provider,
        public string $externalId,
        public PaymentMethod $method,
        public int $amount,
        public DateTimeInterface $expiresAt,
        public string $code,
        public string $instructions,
        public array $metadata = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'provider' => $this->provider,
            'external_id' => $this->externalId,
            'method' => $this->method->value,
            'amount' => $this->amount,
            'expires_at' => $this->expiresAt->format(DATE_ATOM),
            'code' => $this->code,
            'instructions' => $this->instructions,
            'metadata' => $this->metadata,
        ];
    }
}
