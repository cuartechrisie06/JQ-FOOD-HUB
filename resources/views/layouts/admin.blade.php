<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin - JQ Food Hub')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --jq-yellow: #f7c948; --jq-red: #b42318; --jq-black: #171717; }
        .sidebar { width: 240px; min-height: 100vh; background: var(--jq-black); }
        .sidebar a { color: white; text-decoration: none; display: block; padding: 10px 16px; }
        .sidebar a:hover, .sidebar a.active { background: var(--jq-yellow); color: var(--jq-black); }
        .admin-main { width: calc(100% - 240px); }
        .topbar { background: var(--jq-yellow); }
        .btn-jq { background: var(--jq-red); border-color: var(--jq-red); color: white; }
        @media (max-width: 991.98px) {
            .sidebar { width: 100%; min-height: auto; }
            .admin-main { width: 100%; }
            .sidebar nav { display: flex; flex-wrap: wrap; }
            .sidebar nav a { flex: 1 1 150px; }
        }
    </style>
</head>
<body class="d-flex flex-wrap">
<aside class="sidebar">
    <div class="p-3 text-white fw-bold">JQ Food Hub</div>
    <nav>
        <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>
        <a class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">Orders</a>
        <a class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}">Categories</a>
        <a class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}">Products</a>
        <a class="{{ request()->routeIs('admin.packages.*') ? 'active' : '' }}" href="{{ route('admin.packages.index') }}">Food Packages</a>
        <a class="{{ request()->routeIs('admin.catering-bookings.*') ? 'active' : '' }}" href="{{ route('admin.catering-bookings.index') }}">Catering Bookings</a>
        <a class="{{ request()->routeIs('admin.availability.*') ? 'active' : '' }}" href="{{ route('admin.availability.index') }}">Availability Calendar</a>
        <a class="{{ request()->routeIs('admin.prep-sheet*') ? 'active' : '' }}" href="{{ route('admin.prep-sheet') }}">Daily Prep Sheet</a>
        <a class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}">Reports</a>
        <a class="{{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}" href="{{ route('admin.reviews.index') }}">Manage Reviews</a>
        <a class="{{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}" href="{{ route('admin.notifications.index') }}">
            <span aria-hidden="true">&#128276;</span> Notifications
            @php($unreadCount = auth()->user()->notifications()->where('is_read', false)->count())
            @if($unreadCount > 0)<span class="badge text-bg-danger">{{ $unreadCount }}</span>@endif
        </a>
        @if(auth()->user()->role->role_name === 'Owner')
            <a class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">Users</a>
        @endif
        <a href="{{ route('home') }}">Public Home</a>
    </nav>
</aside>
<main class="admin-main">
    <div class="topbar px-4 py-3 d-flex justify-content-between align-items-center">
        <strong>{{ auth()->user()->full_name }}</strong>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-sm btn-dark" type="submit">Logout</button>
        </form>
    </div>
    <div class="p-4">
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif
        @yield('content')
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@vite(['resources/js/app.js'])
</body>
</html>
