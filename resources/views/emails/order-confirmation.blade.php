<h1>Order confirmed</h1>
<p>Hello {{ $order->user->full_name }},</p>
<p>Your order JQ-{{ str_pad($order->order_id, 4, '0', STR_PAD_LEFT) }} has been confirmed.</p>
<p>Scheduled for {{ $order->calendar->slot_date->format('M j, Y') }} at {{ substr($order->fulfillment_time, 0, 5) }}.</p>
<p>Total: PHP {{ number_format($order->total_amount, 2) }}.</p>
