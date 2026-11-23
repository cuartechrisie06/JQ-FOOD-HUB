<h1>Order status update</h1>
<p>Hello {{ $order->user->full_name }},</p>
<p>Order JQ-{{ str_pad($order->order_id, 4, '0', STR_PAD_LEFT) }} changed from {{ $previousStatus }} to {{ $newStatus }}.</p>
