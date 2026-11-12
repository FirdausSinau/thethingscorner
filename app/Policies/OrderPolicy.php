<?php

namespace App\Policies;

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
}