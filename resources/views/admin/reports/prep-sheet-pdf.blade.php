<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>Daily Prep Sheet</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
    h1 { font-size: 20px; } h2 { font-size: 15px; margin-top: 20px; }
    table { border-collapse: collapse; width: 100%; margin-top: 8px; }
    th, td { border: 1px solid #777; padding: 6px; text-align: left; }
    th { background: #f7c948; }
</style></head>
<body>
<h1>Daily Prep Sheet</h1><p>{{ $date->format('l, F j, Y') }}</p>
<h2>Product Preparation</h2>
<table><thead><tr><th>Product</th><th>Total quantity</th></tr></thead><tbody>
@forelse($sheet['products'] as $product)<tr><td>{{ $product['name'] }}</td><td>{{ $product['quantity'] }}</td></tr>
@empty<tr><td colspan="2">No product preparation for this date.</td></tr>@endforelse
</tbody></table>
<h2>Packages</h2>
<table><thead><tr><th>Package</th><th>Quantity</th><th>Order</th><th>Customer</th></tr></thead><tbody>
@forelse($sheet['packages'] as $package)<tr><td>{{ $package['name'] }}</td><td>{{ $package['quantity'] }}</td><td>{{ $package['order'] }}</td><td>{{ $package['customer'] }}</td></tr>
@empty<tr><td colspan="4">No packages for this date.</td></tr>@endforelse
</tbody></table>
<h2>Catering Events</h2>
<table><thead><tr><th>Package</th><th>Guests</th><th>Time</th><th>Location</th><th>Order</th><th>Customer</th></tr></thead><tbody>
@forelse($sheet['events'] as $event)<tr><td>{{ $event['package'] }}</td><td>{{ $event['guests'] }}</td><td>{{ $event['time'] }}</td><td>{{ $event['location'] }}</td><td>{{ $event['order'] }}</td><td>{{ $event['customer'] }}</td></tr>
@empty<tr><td colspan="6">No catering events for this date.</td></tr>@endforelse
</tbody></table>
</body></html>
