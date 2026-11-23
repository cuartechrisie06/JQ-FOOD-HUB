<?php

namespace App\Observers;

use App\Models\Payment;
use App\Services\OrderNotificationService;

class PaymentObserver
{
    public function created(Payment $payment): void
    {
        if ($payment->status === 'Pending') {
            app(OrderNotificationService::class)
                ->notifyAdminsOfPendingPayment($payment->order);
        }
    }
}
