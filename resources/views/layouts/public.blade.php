<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'JQ Food Hub - Authentic Lumpia & Catering')</title>
    <meta name="description" content="@yield('meta_description', 'Freshly rolled lumpia, packed meals, and event catering packages in Tagum City, Davao del Norte. Order for pickup or delivery.')">
    <meta name="theme-color" content="#f7c948">
    <meta property="og:site_name" content="JQ Food Hub">
    <meta property="og:title" content="@yield('title', 'JQ Food Hub - Authentic Lumpia & Catering')">
    <meta property="og:description" content="@yield('meta_description', 'Freshly rolled lumpia, packed meals, and event catering packages in Tagum City.')">
    <meta property="og:image" content="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'FoodEstablishment',
            'name' => 'JQ Food Hub',
            'url' => url('/'),
            'image' => asset('images/logo.png'),
            'servesCuisine' => 'Filipino',
            'description' => 'Handmade lumpia, packed meals, and catering packages in Tagum City.',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'Villa Patricia Subd., Mankilam',
                'addressLocality' => 'Tagum City',
                'addressRegion' => 'Davao del Norte',
                'addressCountry' => 'PH',
            ],
            'openingHours' => 'Mo-Sa 08:00-18:00',
            'sameAs' => ['https://www.facebook.com/profile.php?id=100085901478582'],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
    </script>
</head>
<body>
<a class="skip-link" href="#main">Skip to main content</a>

@php($isCustomer = auth()->check() && auth()->user()->role->role_name === 'Customer')
@php($isStaff = auth()->check() && in_array(auth()->user()->role->role_name, ['Admin', 'Owner'], true))

<nav class="navbar navbar-expand-lg navbar-jq sticky-top" aria-label="Main navigation">
    <div class="container">
        <a class="navbar-brand-jq" href="{{ route('home') }}">
            <img src="{{ asset('images/logo.png') }}" alt="" class="logo-badge" width="44" height="44">
            <span>JQ Food Hub</span>
        </a>

        <button class="navbar-toggler px-2 py-1" type="button" data-bs-toggle="collapse" data-bs-target="#publicNav" aria-controls="publicNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="publicNav">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-lg-1">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>
                        <i class="bi bi-house d-lg-none" aria-hidden="true"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('menu.*', 'products.*', 'packages.*') ? 'active' : '' }}" href="{{ route('menu.index') }}">
                        <i class="bi bi-journal-richtext d-lg-none" aria-hidden="true"></i> Menu
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('reviews') ? 'active' : '' }}" href="{{ route('reviews') }}">
                        <i class="bi bi-star d-lg-none" aria-hidden="true"></i> Reviews
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
                @php($cartCount = is_array(session('cart')) ? array_sum(session('cart')) : 0)
                <a class="btn btn-light position-relative border px-3 py-2 d-inline-flex align-items-center gap-2 {{ request()->routeIs('cart.*', 'checkout.*') ? 'border-danger text-danger' : 'text-dark' }}" href="{{ auth()->check() ? route('cart.index') : route('login') }}" aria-label="Shopping cart{{ $cartCount > 0 ? ', '.$cartCount.' items' : '' }}">
                    <i class="bi bi-cart3 fs-5" aria-hidden="true"></i>
                    <span class="d-lg-none fw-semibold">Cart</span>
                    @if($cartCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light" aria-hidden="true">{{ $cartCount }}</span>
                    @endif
                </a>

                @auth
                    @if($isCustomer)
                        <div class="dropdown">
                            <button class="btn btn-outline-dark dropdown-toggle px-3 py-2 d-inline-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle" aria-hidden="true"></i>
                                <span class="fw-semibold">{{ Str::limit(auth()->user()->full_name, 12) }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                                <li><a class="dropdown-item py-2" href="{{ route('orders.index') }}"><i class="bi bi-receipt me-2" aria-hidden="true"></i> My Orders</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('reviews.mine') }}"><i class="bi bi-star me-2" aria-hidden="true"></i> My Reviews</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('account.edit') }}"><i class="bi bi-gear me-2" aria-hidden="true"></i> Account Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button class="dropdown-item py-2 text-danger" type="submit">
                                            <i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @elseif($isStaff)
                        <a class="btn btn-jq-dark d-inline-flex align-items-center gap-2" href="{{ route('admin.dashboard') }}">
                            <i class="bi bi-speedometer2" aria-hidden="true"></i> Admin Panel
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button class="btn btn-outline-dark" type="submit" aria-label="Log out" title="Log out">
                                <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                            </button>
                        </form>
                    @endif
                @else
                    <a class="btn btn-outline-dark px-3 py-2 fw-semibold" href="{{ route('login') }}">Login</a>
                    <a class="btn btn-jq px-3 py-2" href="{{ route('register') }}">Register</a>
                @endauth
            </div>
        </div>
    </div>
</nav>

<main id="main" class="container py-4 flex-grow-1">
    @include('partials.flash')

    @yield('content')
</main>

<footer class="footer-jq py-5 mt-auto">
    <div class="container">
        <div class="row g-4 align-items-center justify-content-between pb-4 border-bottom border-secondary border-opacity-25">
            <div class="col-md-5">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <img src="{{ asset('images/logo.png') }}" alt="" class="logo-badge" width="44" height="44" style="border:0">
                    <span class="fs-4 fw-bold text-white">JQ Food Hub</span>
                </div>
                <p class="text-white-50 mb-0">Crispy, homemade lumpia and catering packages across Tagum City and the Davao Region.</p>
            </div>
            <div class="col-md-6 col-lg-5 text-md-end">
                <nav aria-label="Footer" class="d-flex flex-wrap gap-3 justify-content-md-end mb-3">
                    <a href="{{ route('home') }}" class="footer-link">Home</a>
                    <a href="{{ route('menu.index') }}" class="footer-link">Menu</a>
                    <a href="{{ route('reviews') }}" class="footer-link">Reviews</a>
                    <a href="{{ route('faq') }}" class="footer-link">FAQs</a>
                    <a href="{{ route('about') }}" class="footer-link">Our Story</a>
                    <a href="{{ route('contact') }}" class="footer-link">Contact &amp; Location</a>
                </nav>
                <div class="d-flex gap-2 justify-content-md-end">
                    <a href="https://www.facebook.com/profile.php?id=100085901478582" target="_blank" rel="noopener" class="btn btn-sm btn-outline-light px-3 rounded-pill d-inline-flex align-items-center gap-2">
                        <i class="bi bi-facebook" aria-hidden="true"></i> Facebook
                    </a>
                    <a href="https://m.me/100085901478582" target="_blank" rel="noopener" class="btn btn-sm btn-jq-gold px-3 rounded-pill d-inline-flex align-items-center gap-2">
                        <i class="bi bi-messenger" aria-hidden="true"></i> Messenger
                    </a>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 pt-4 text-white-50 small">
            <div>
                <i class="bi bi-geo-alt-fill text-warning me-1" aria-hidden="true"></i> Villa Patricia Subd., Mankilam, Tagum City, Davao del Norte
                <span class="d-block d-md-inline ms-md-3"><i class="bi bi-clock me-1" aria-hidden="true"></i> Mon&ndash;Sat 8:00 AM &ndash; 6:00 PM</span>
            </div>
            <div>&copy; {{ date('Y') }} JQ Food Hub. All rights reserved.</div>
        </div>
    </div>
</footer>

@include('partials.confirm-modal')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
