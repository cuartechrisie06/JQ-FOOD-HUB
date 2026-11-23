<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>JQ Food Hub Report</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
    h1 { font-size: 20px; } h2 { font-size: 15px; margin-top: 20px; }
    table { border-collapse: collapse; width: 100%; margin-top: 8px; }
    th, td { border: 1px solid #777; padding: 6px; text-align: left; }
    th { background: #f7c948; }
</style></head>
<body>
<h1>JQ Food Hub Report</h1>
<p>{{ $from->format('M j, Y') }} to {{ $to->format('M j, Y') }}</p>
<p>Orders: {{ $ordersCount }} | Verified Sales: ₱{{ number_format((float) $totalSales, 2) }}</p>
<h2>Orders by Status</h2><table><thead><tr><th>Status</th><th>Orders</th></tr></thead><tbody>
@foreach($statusCounts as $status => $count)<tr><td>{{ $status }}</td><td>{{ $count }}</td></tr>@endforeach
</tbody></table>
<h2>Verified Sales by Date</h2><table><thead><tr><th>Date</th><th>Verified Sales</th></tr></thead><tbody>
@foreach($dailySales as $sale)<tr><td>{{ $sale['date'] }}</td><td>₱{{ number_format($sale['amount'], 2) }}</td></tr>@endforeach
</tbody></table>
<h2>Best-Selling Products</h2><table><thead><tr><th>Product</th><th>Quantity sold</th></tr></thead><tbody>
@forelse($bestSellers as $product)<tr><td>{{ $product->product_name }}</td><td>{{ $product->quantity_sold }}</td></tr>
@empty<tr><td colspan="2">No product sales in this period.</td></tr>@endforelse
</tbody></table>
</body></html>
