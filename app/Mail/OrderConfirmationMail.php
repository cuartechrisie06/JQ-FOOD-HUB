<?php

namespace App\Mail;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
        $this->order->loadMissing(['calendar', 'user', 'booking']);
    }

    public function build(): self
    {
        return $this->subject('Order confirmed - JQ-'.str_pad($this->order->order_id, 4, '0', STR_PAD_LEFT))
            ->view('emails.order-confirmation')
            ->attachData($this->calendarFile(), 'jq-order-'.$this->order->order_id.'.ics', [
                'mime' => 'text/calendar; charset=UTF-8; method=PUBLISH',
            ]);
    }

    public function attachments(): array
    {
        return [];
    }

    private function calendarFile(): string
    {
        $date = $this->order->calendar->slot_date->toDateString();
        $start = Carbon::parse($date.' '.$this->order->fulfillment_time)->utc();
        $end = $start->copy()->addHour();
        $title = $this->order->order_type === 'Catering'
            ? 'JQ Food Hub Catering Event'
            : 'JQ Food Hub Order Pickup/Delivery';
        $location = $this->order->delivery_address ?? 'JQ Food Products, Mankilam, Tagum City';
        $uid = 'jq-order-'.$this->order->order_id.'@'.(parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost');

        return implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//JQ Food Hub//Order Calendar//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:'.$uid,
            'DTSTAMP:'.now()->utc()->format('Ymd\THis\Z'),
            'DTSTART:'.$start->format('Ymd\THis\Z'),
            'DTEND:'.$end->format('Ymd\THis\Z'),
            'SUMMARY:'.$this->escapeCalendarText($title),
            'DESCRIPTION:'.$this->escapeCalendarText('Order JQ-'.str_pad($this->order->order_id, 4, '0', STR_PAD_LEFT)),
            'LOCATION:'.$this->escapeCalendarText($location),
            'END:VEVENT',
            'END:VCALENDAR',
            '',
        ]);
    }

    private function escapeCalendarText(string $value): string
    {
        return str_replace(
            ['\\', ';', ',', "\r\n", "\n"],
            ['\\\\', '\\;', '\\,', '\\n', '\\n'],
            $value
        );
    }
}
