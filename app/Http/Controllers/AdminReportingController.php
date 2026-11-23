<?php

namespace App\Http\Controllers;

use App\Exports\ArrayExport;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class AdminReportingController extends Controller
{
    public function dashboard(): View
    {
        $today = today();
        $chartStart = $today->copy()->subDays(6);
        $salesByDate = Payment::query()
            ->where('status', 'Verified')
            ->whereBetween('verified_at', [$chartStart->copy()->startOfDay(), $today->copy()->endOfDay()])
            ->selectRaw('DATE(verified_at) as sales_date, SUM(amount) as total')
            ->groupBy('sales_date')
            ->pluck('total', 'sales_date');

        $chartLabels = [];
        $chartValues = [];
        for ($date = $chartStart->copy(); $date <= $today; $date->addDay()) {
            $key = $date->toDateString();
            $chartLabels[] = $date->format('M j');
            $chartValues[] = (float) ($salesByDate[$key] ?? 0);
        }

        return view('admin.dashboard', [
            'newOrders' => Order::where('status', 'Pending')->count(),
            'pendingPayments' => Payment::where('status', 'Pending')->count(),
            'deliveriesToday' => Order::where('fulfillment_method', 'Delivery')
                ->whereNotIn('status', ['Cancelled', 'Completed'])
                ->whereHas('calendar', fn ($query) => $query->whereDate('slot_date', $today))
                ->count(),
            'salesThisMonth' => Payment::where('status', 'Verified')
                ->whereBetween('verified_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('amount'),
            'chartLabels' => $chartLabels,
            'chartValues' => $chartValues,
            'recentOrders' => Order::with(['user', 'calendar'])
                ->orderByDesc('created_at')
                ->limit(8)
                ->get(),
        ]);
    }

    public function prepSheet(Request $request): View
    {
        $date = $this->validatedDate($request);
        $sheet = $this->buildPrepSheet($date);

        return view('admin.reports.prep-sheet', [
            'date' => $date,
            'sheet' => $sheet,
        ]);
    }

    public function prepSheetPdf(Request $request): Response
    {
        $date = $this->validatedDate($request);

        return Pdf::loadView('admin.reports.prep-sheet-pdf', [
            'date' => $date,
            'sheet' => $this->buildPrepSheet($date),
        ])->download('daily-prep-sheet-'.$date->toDateString().'.pdf');
    }

    public function prepSheetCsv(Request $request): BinaryFileResponse
    {
        $date = $this->validatedDate($request);
        $sheet = $this->buildPrepSheet($date);
        $rows = [];

        foreach ($sheet['products'] as $product) {
            $rows[] = ['Product', $product['name'], $product['quantity'], '', '', $date->toDateString(), '', ''];
        }
        foreach ($sheet['packages'] as $package) {
            $rows[] = ['Package', $package['name'], $package['quantity'], $package['order'], $package['customer'], $date->toDateString(), '', ''];
        }
        foreach ($sheet['events'] as $event) {
            $rows[] = ['Catering Event', $event['package'], $event['guests'], $event['order'], $event['customer'], $date->toDateString(), $event['time'], $event['location']];
        }

        return Excel::download(new ArrayExport(
            ['Type', 'Name', 'Quantity/Guests', 'Order', 'Customer', 'Date', 'Time', 'Location'],
            $rows
        ), 'daily-prep-sheet-'.$date->toDateString().'.csv');
    }

    public function reports(Request $request): View
    {
        $data = $this->buildReportData($request);

        return view('admin.reports.index', $data);
    }

    public function reportsPdf(Request $request): Response
    {
        return Pdf::loadView('admin.reports.pdf', $this->buildReportData($request))
            ->download('jq-food-hub-report.pdf');
    }

    public function reportsCsv(Request $request): BinaryFileResponse
    {
        $data = $this->buildReportData($request);
        $rows = [
            ['Summary', 'Orders', $data['ordersCount']],
            ['Summary', 'Verified Sales', number_format((float) $data['totalSales'], 2, '.', '')],
        ];

        foreach ($data['statusCounts'] as $status => $count) {
            $rows[] = ['Order Status', $status, $count];
        }
        foreach ($data['dailySales'] as $sale) {
            $rows[] = ['Verified Sales by Date', $sale['date'], number_format($sale['amount'], 2, '.', '')];
        }
        foreach ($data['bestSellers'] as $product) {
            $rows[] = ['Best Seller', $product->product_name, $product->quantity_sold];
        }

        return Excel::download(new ArrayExport(
            ['Section', 'Name', 'Value'],
            $rows
        ), 'jq-food-hub-report.csv');
    }

    private function validatedDate(Request $request): Carbon
    {
        $validated = $request->validate(['date' => ['nullable', 'date']]);

        return Carbon::parse($validated['date'] ?? today()->toDateString())->startOfDay();
    }

    private function buildPrepSheet(Carbon $date): array
    {
        $orders = Order::with(['items.product', 'items.foodPackage', 'user', 'booking.foodPackage'])
            ->whereIn('status', ['Confirmed', 'Preparing'])
            ->whereHas('calendar', fn ($query) => $query->whereDate('slot_date', $date))
            ->get();

        $products = [];
        $packages = [];
        $events = [];

        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                if ($item->product) {
                    $id = $item->product->product_id;
                    $products[$id] ??= ['name' => $item->product->product_name, 'quantity' => 0];
                    $products[$id]['quantity'] += $item->quantity;
                }

                if ($item->foodPackage) {
                    $packages[] = [
                        'name' => $item->foodPackage->package_name,
                        'quantity' => $item->quantity,
                        'order' => 'JQ-'.str_pad((string) $order->order_id, 4, '0', STR_PAD_LEFT),
                        'customer' => $order->user->full_name,
                    ];
                }
            }

            if ($order->booking) {
                $events[] = [
                    'package' => $order->booking->foodPackage->package_name,
                    'guests' => $order->booking->guest_count,
                    'order' => 'JQ-'.str_pad((string) $order->order_id, 4, '0', STR_PAD_LEFT),
                    'customer' => $order->user->full_name,
                    'time' => Carbon::parse($order->booking->event_time)->format('g:i A'),
                    'location' => $order->booking->event_location,
                ];
            }
        }

        return [
            'products' => array_values($products),
            'packages' => $packages,
            'events' => $events,
        ];
    }

    private function buildReportData(Request $request): array
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $from = Carbon::parse($validated['from'] ?? now()->startOfMonth()->toDateString())->startOfDay();
        $to = Carbon::parse($validated['to'] ?? today()->toDateString())->endOfDay();

        $orders = Order::whereHas('calendar', fn ($query) => $query
            ->whereDate('slot_date', '>=', $from->toDateString())
            ->whereDate('slot_date', '<=', $to->toDateString()));
        $statusCounts = [];
        foreach (['Pending', 'Confirmed', 'Preparing', 'Ready', 'Out for Delivery', 'Completed', 'Cancelled'] as $status) {
            $statusCounts[$status] = (clone $orders)->where('status', $status)->count();
        }

        $bestSellers = OrderItem::query()
            ->join('orders', 'orders.order_id', '=', 'order_items.order_id')
            ->join('availability_calendar', 'availability_calendar.calendar_id', '=', 'orders.calendar_id')
            ->join('products', 'products.product_id', '=', 'order_items.product_id')
            ->whereDate('availability_calendar.slot_date', '>=', $from->toDateString())
            ->whereDate('availability_calendar.slot_date', '<=', $to->toDateString())
            ->whereNotIn('orders.status', ['Cancelled'])
            ->whereNotNull('order_items.product_id')
            ->selectRaw('products.product_name, SUM(order_items.quantity) as quantity_sold')
            ->groupBy('products.product_id', 'products.product_name')
            ->orderByDesc('quantity_sold')
            ->limit(10)
            ->get();

        $salesByDate = Payment::where('status', 'Verified')
            ->whereBetween('verified_at', [$from, $to])
            ->selectRaw('DATE(verified_at) as sales_date, SUM(amount) as total')
            ->groupBy('sales_date')
            ->orderBy('sales_date')
            ->get()
            ->keyBy('sales_date');

        $chartLabels = [];
        $chartValues = [];
        $dailySales = [];
        for ($date = $from->copy()->startOfDay(); $date <= $to; $date->addDay()) {
            $key = $date->toDateString();
            $chartLabels[] = $date->format('M j');
            $amount = (float) ($salesByDate[$key]->total ?? 0);
            $chartValues[] = $amount;
            $dailySales[] = ['date' => $key, 'amount' => $amount];
        }

        return [
            'from' => $from,
            'to' => $to,
            'ordersCount' => (clone $orders)->count(),
            'totalSales' => Payment::where('status', 'Verified')
                ->whereBetween('verified_at', [$from, $to])
                ->sum('amount'),
            'statusCounts' => $statusCounts,
            'bestSellers' => $bestSellers,
            'chartLabels' => $chartLabels,
            'chartValues' => $chartValues,
            'dailySales' => $dailySales,
        ];
    }
}
