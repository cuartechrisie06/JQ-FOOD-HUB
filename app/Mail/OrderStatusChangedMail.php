<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChangedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $previousStatus,
        public string $newStatus
    ) {
        $this->order->loadMissing(['user', 'calendar']);
    }

    public function build(): self
    {
        return $this->subject('Order update - JQ-'.str_pad($this->order->order_id, 4, '0', STR_PAD_LEFT))
            ->view('emails.order-status-changed');
    }
}
