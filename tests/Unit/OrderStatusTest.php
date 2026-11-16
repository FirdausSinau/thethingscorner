<?php

namespace Tests\Unit;

use App\Enums\OrderStatus;
use PHPUnit\Framework\TestCase;

class OrderStatusTest extends TestCase
{
    public function test_menunggu_konfirmasi_is_labelled_dipending(): void
    {
        $this->assertSame('Dipending', OrderStatus::MenungguKonfirmasi->label());
    }

    public function test_paid_order_moves_to_confirmation_first(): void
    {
        $this->assertTrue(OrderStatus::Dibayar->canTransitionTo(OrderStatus::MenungguKonfirmasi));
        $this->assertFalse(OrderStatus::Dibayar->canTransitionTo(OrderStatus::Dikemas));
    }

    public function test_confirmation_can_move_to_packing_or_cancel(): void
    {
        $this->assertTrue(OrderStatus::MenungguKonfirmasi->canTransitionTo(OrderStatus::Dikemas));
        $this->assertTrue(OrderStatus::MenungguKonfirmasi->canTransitionTo(OrderStatus::Dibatalkan));
    }

    public function test_final_status_has_no_next_step(): void
    {
        $this->assertTrue(OrderStatus::Selesai->isFinal());
        $this->assertSame([], OrderStatus::Selesai->allowedNext());
    }
}
