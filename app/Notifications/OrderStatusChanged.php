<?php

namespace App\Notifications;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Notifications\Notification;

class OrderStatusChanged extends Notification
{
    public function __construct(
        public Order $order,
        public OrderStatus $from,
        public OrderStatus $to,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_code' => $this->order->order_code,
            'from_status' => $this->from->value,
            'to_status' => $this->to->value,
            'title' => 'Status pesanan diperbarui',
            'body' => "Pesanan {$this->order->order_code} sekarang {$this->to->label()}.",
            'url' => route('orders.show', ['order' => $this->order->order_code]),
        ];
    }
}
