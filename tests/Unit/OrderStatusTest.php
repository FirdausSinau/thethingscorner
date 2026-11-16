<?php

namespace Tests\Unit;

use App\Enums\OrderStatus;
use PHPUnit\Framework\TestCase;

class OrderStatusTest extends TestCase
{
    public function test_dipending_status_is_labelled_dipending(): void
    {
        $this->assertSame('Dipending', OrderStatus::Dipending->label());
        $this->assertSame('DIPENDING', OrderStatus::Dipending->value);
    }

    public function test_paid_order_moves_to_packing(): void
    {
        $this->assertTrue(OrderStatus::Dibayar->canTransitionTo(OrderStatus::Dikemas));
        $this->assertFalse(OrderStatus::Dibayar->canTransitionTo(OrderStatus::Dipending));
        $this->assertFalse(OrderStatus::Dibayar->canTransitionTo(OrderStatus::Dibatalkan));
    }

    public function test_packing_can_move_to_shipping_or_dipending(): void
    {
        $this->assertTrue(OrderStatus::Dikemas->canTransitionTo(OrderStatus::Dikirim));
        $this->assertTrue(OrderStatus::Dikemas->canTransitionTo(OrderStatus::Dipending));
        $this->assertFalse(OrderStatus::Dikemas->canTransitionTo(OrderStatus::Dibatalkan));
    }

    public function test_dipending_can_move_to_shipping_or_cancel(): void
    {
        $this->assertTrue(OrderStatus::Dipending->canTransitionTo(OrderStatus::Dikirim));
        $this->assertTrue(OrderStatus::Dipending->canTransitionTo(OrderStatus::Dibatalkan));
        $this->assertFalse(OrderStatus::Dipending->canTransitionTo(OrderStatus::Dikemas));
    }

    public function test_shipping_can_only_finish(): void
    {
        $this->assertTrue(OrderStatus::Dikirim->canTransitionTo(OrderStatus::Selesai));
        $this->assertFalse(OrderStatus::Dikirim->canTransitionTo(OrderStatus::Dibatalkan));
    }

    public function test_final_status_has_no_next_step(): void
    {
        $this->assertTrue(OrderStatus::Selesai->isFinal());
        $this->assertSame([], OrderStatus::Selesai->allowedNext());
    }
}
