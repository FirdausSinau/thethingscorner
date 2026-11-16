<?php

namespace App\Services\Payment;

use App\Enums\PaymentMethod;
use App\Models\Order;

interface PaymentGateway
{
    public function name(): string;

    public function create(Order $order, PaymentMethod $method): PaymentInstruction;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function parseCallback(array $payload): PaymentEvent;
}
