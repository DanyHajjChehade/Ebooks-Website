<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Customers may only see their own orders. Admins use the admin area.
     */
    public function view(User $user, Order $order): bool
    {
        return $order->user_id !== null && $order->user_id === $user->getKey();
    }
}
