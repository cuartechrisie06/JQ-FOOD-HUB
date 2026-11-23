<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function createReview(User $user, Order $order): bool
    {
        return $order->user_id === $user->user_id
            && $order->status === 'Completed'
            && ! $order->review()->exists();
    }
}
