<?php

namespace App\Policies;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    public function view(User $user, Order $order): Response
    {
        if ($user->can('manage-orders') || $order->user_id === $user->id) {
            return Response::allow();
        }

        return Response::denyAsNotFound();
    }

    public function cancel(User $user, Order $order): Response
    {
        if ($order->user_id !== $user->id) {
            return Response::denyAsNotFound();
        }

        if (! in_array($order->status, [OrderStatus::MenungguPembayaran, OrderStatus::Dipending], true)) {
            return Response::deny('Pesanan ini sudah tidak bisa dibatalkan.');
        }

        return Response::allow();
    }

    public function confirm(User $user, Order $order): Response
    {
        if ($order->user_id !== $user->id) {
            return Response::denyAsNotFound();
        }

        if ($order->status !== OrderStatus::Dikirim) {
            return Response::deny('Konfirmasi terima hanya tersedia saat pesanan dikirim.');
        }

        return Response::allow();
    }
}
