<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingConfirmedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
        $this->order->loadMissing(['calendar', 'user']);
    }

    public function build(): self
    {
        return $this->subject('Catering request approved - JQ-'.str_pad($this->order->order_id, 4, '0', STR_PAD_LEFT))
            ->view('emails.booking-confirmed');
    }
}
