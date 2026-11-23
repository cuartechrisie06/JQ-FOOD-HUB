<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\OrderNotificationService;

class OrderObserver
{
    public function created(Order $order): void
    {
        app(OrderNotificationService::class)->notifyAdminsOfNewOrder($order);
    }
}
