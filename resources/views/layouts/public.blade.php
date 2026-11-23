<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'JQ Food Hub')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        :root { --jq-yellow: #f7c948; --jq-red: #b42318; --jq-black: #171717; }
        body { background: #fffdf5; color: var(--jq-black); }
        .navbar-jq { background: var(--jq-yellow); }
        .navbar-jq .nav-link, .navbar-jq .navbar-brand { color: var(--jq-black); font-weight: 600; }
        .navbar-jq .nav-link.active { color: var(--jq-red); }
        .btn-jq { background: var(--jq-red); border-color: var(--jq-red); color: white; }
        .btn-jq:hover { background: #8f1b13; border-color: #8f1b13; color: white; }
        .product-image { height: 220px; object-fit: cover; width: 100%; }
        .footer-jq { background: var(--jq-black); color: white; }
        .footer-jq a { color: var(--jq-yellow); text-decoration: none; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-jq">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">JQ Food Hub</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#publicNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="publicNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">Products</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('packages.*') ? 'active' : '' }}" href="{{ route('packages.index') }}">Packages</a></li>
                @auth
                    @if(auth()->user()->role->role_name === 'Customer')
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('catering.*') ? 'active' : '' }}" href="{{ route('catering.index') }}">Catering</a></li>
                    @else
                        <li class="nav-item"><a class="nav-link" href="{{ route('packages.index') }}">Catering</a></li>
                    @endif
                @else
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Catering</a></li>
                @endauth
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('reviews') ? 'active' : '' }}" href="{{ route('reviews') }}">Reviews</a></li>
                @auth
                    @if(auth()->user()->role->role_name === 'Customer')
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('reviews.mine') ? 'active' : '' }}" href="{{ route('reviews.mine') }}">My Reviews</a></li>
                    @endif
                @endauth
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">About</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">Contact</a></li>
                @auth
                    @if(auth()->user()->role->role_name === 'Customer')
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('cart.*', 'checkout.*') ? 'active' : '' }}" href="{{ route('cart.index') }}">Cart</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('orders.*') ? 'active' : '' }}" href="{{ route('orders.index') }}">My Orders</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('account.*') ? 'active' : '' }}" href="{{ route('account.edit') }}">Account</a></li>
                    @elseif(in_array(auth()->user()->role->role_name, ['Admin', 'Owner'], true))
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}">Admin</a></li>
                    @endif
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="nav-link btn btn-link" type="submit">Logout</button>
                        </form>
                    </li>
                @else
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Cart</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Register</a></li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
<main class="container py-4">
    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @yield('content')
</main>
<footer class="footer-jq py-4 mt-auto">
    <div class="container d-flex flex-wrap justify-content-between gap-3">
        <div>
            <strong>JQ Food Hub</strong>
            <div>Mankilam, Tagum City</div>
        </div>
        <div>
            <a href="{{ route('about') }}" class="me-3">Our Story</a>
            <a href="https://www.facebook.com/" target="_blank" rel="noopener" class="me-3">Facebook</a>
            <a href="https://m.me/" target="_blank" rel="noopener">Messenger</a>
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
