@extends('layouts.public')
@section('title', 'FAQs - JQ Food Hub')
@section('meta_description', 'Answers to common questions about ordering lumpia, catering packages, payment, pickup and delivery from JQ Food Hub in Tagum City.')
@section('content')
@php
    $faqs = [
        'Ordering' => [
            ['How do I place an order?', 'Create an account or log in, add products from the Lumpias tab of our menu to your cart, then check out. You will choose pickup or delivery, the date and time, and how you want to pay.'],
            ['How do I know my order was received?', 'You get a reference number right after checkout (for example JQ-0012). You can follow its status anytime under My Orders, and we email you when it is confirmed.'],
            ['Can I order in bulk or for packed lunches?', 'Yes. Bulk orders and packed lunches need a 50% down payment to secure your slot. The down payment is shown at checkout and on your order page.'],
        ],
        'Payment' => [
            ['What payment methods do you accept?', 'We accept cash and online payments (e-wallet or bank transfer). For online payments, upload a screenshot or photo of your receipt on your order page so we can verify it.'],
            ['Why do I need a down payment?', 'For bulk orders, packed lunches and catering we ask for 50% upfront to secure your slot. The balance is paid on the event or delivery date.'],
            ['I uploaded the wrong proof. What now?', 'If we cannot verify your payment, your order page will say so and let you send a new proof. You can also message us on Messenger.'],
        ],
        'Pickup and delivery' => [
            ['Do you offer pickup and delivery?', 'Yes. Choose Pickup or Delivery at checkout. For delivery, enter the full address so we can bring your order on the agreed schedule.'],
            ['Where do you deliver?', 'We are based in Mankilam, Tagum City and serve customers mainly in Tagum City and other areas of the Davao Region. Contact us if you are not sure we cover your area.'],
        ],
        'Catering and food packages' => [
            ['How do I book catering?', 'Log in, open Book Catering, pick an open date on the calendar, choose a package and fill in your event details. We review your request and, once confirmed, you pay the 50% down payment on your order page.'],
            ['How far ahead should I book?', 'Catering requests must be made at least 3 days before the event. Dates marked Fully booked or Closed on the calendar cannot be selected.'],
            ['What is the minimum number of guests?', 'Budget Meals, Snacks and Buffet Meals are priced per person with a minimum of 25 guests. Fixed-price Food Packages are priced per tray set.'],
            ['Can I add drinks?', 'Per-person packages can be booked solo, with bottled water, or with softdrinks. The price for each option is shown on the package.'],
        ],
        'Our products' => [
            ['What do you sell?', 'We make fresh lumpia, lumpia ubod, lumpia shanghai and cheesesticks, plus packed lunches, catering and food packages for events.'],
            ['Do you supply restaurants and meat shops?', 'Yes. Businesses can register as a Business customer and order in bulk. Message us for regular supply arrangements.'],
        ],
    ];
@endphp

<div class="mb-4">
    <span class="badge bg-warning-subtle text-warning-emphasis fw-bold mb-1"><i class="bi bi-question-circle" aria-hidden="true"></i> FAQs</span>
    <h1 class="h2 fw-bold mb-1">Frequently Asked Questions</h1>
    <p class="text-muted mb-0">Quick answers about ordering, payment, delivery and catering.</p>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        @foreach($faqs as $section => $items)
            <h2 class="h5 fw-bold mb-3 {{ $loop->first ? '' : 'mt-4' }}">{{ $section }}</h2>
            <div class="accordion" id="faq-{{ $loop->index }}">
                @foreach($items as $i => $item)
                    @php($id = "faq-{$loop->parent->index}-{$i}")
                    <div class="accordion-item">
                        <h3 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $id }}" aria-expanded="false" aria-controls="{{ $id }}">{{ $item[0] }}</button>
                        </h3>
                        <div id="{{ $id }}" class="accordion-collapse collapse" data-bs-parent="#faq-{{ $loop->parent->index }}">
                            <div class="accordion-body text-secondary">{{ $item[1] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
    <div class="col-lg-4">
        <div class="card p-4 sticky-top" style="top: 100px;">
            <h2 class="h5 fw-bold">Still have a question?</h2>
            <p class="text-muted small">Message us and we will get back to you.</p>
            <a class="btn btn-jq w-100" href="{{ route('contact') }}"><i class="bi bi-chat-dots" aria-hidden="true"></i> Contact us</a>
        </div>
    </div>
</div>
@endsection
