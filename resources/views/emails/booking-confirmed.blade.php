<h1>Your catering request was approved</h1>
<p>Hello {{ $order->user->full_name }},</p>
<p>Your catering request for {{ $order->calendar->slot_date->format('M j, Y') }} at {{ substr($order->fulfillment_time, 0, 5) }} has been approved. Your reference number is JQ-{{ str_pad($order->order_id, 4, '0', STR_PAD_LEFT) }}.</p>
<p>To secure your slot, please send the 50% down payment of <strong>PHP {{ number_format($order->downpayment_amount, 2) }}</strong> (total PHP {{ number_format($order->total_amount, 2) }}). The balance is paid on the event date.</p>
<p>Log in and open <a href="{{ route('orders.show', $order) }}">your order</a> to see the payment details and upload your proof of payment.</p>
