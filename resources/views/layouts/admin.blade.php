<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Portal - JQ Food Hub')</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<a class="skip-link" href="#main">Skip to main content</a>

<div class="admin-wrapper">
    {{-- Sidebar: a fixed column on desktop, an off-canvas drawer on phones/tablets --}}
    <aside class="admin-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="adminSidebar" aria-label="Admin navigation">
        <div class="admin-sidebar-inner">
            <div class="d-flex align-items-stretch">
                <a href="{{ route('admin.dashboard') }}" class="sidebar-brand flex-grow-1">
                    <img src="{{ asset('images/logo.png') }}" alt="" class="logo-badge logo-badge-sm" width="38" height="38">
                    <div>
                        <div class="fw-bold fs-6 text-white text-nowrap">JQ Food Hub</div>
                        <div class="text-warning" style="font-size: 0.72rem;">Management Portal</div>
                    </div>
                </a>
                <button type="button" class="btn-close btn-close-white d-lg-none align-self-center mx-3" data-bs-dismiss="offcanvas" data-bs-target="#adminSidebar" aria-label="Close menu"></button>
            </div>

            <nav class="sidebar-nav" aria-label="Admin sections">
                <div class="nav-header">Overview</div>
                <a class="nav-item-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}" @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif>
                    <span class="link-inner"><i class="bi bi-speedometer2" aria-hidden="true"></i> Dashboard</span>
                </a>

                <div class="nav-header">Operations</div>
                <a class="nav-item-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">
                    <span class="link-inner"><i class="bi bi-receipt" aria-hidden="true"></i> Orders</span>
                </a>
                <a class="nav-item-link {{ request()->routeIs('admin.prep-sheet*') ? 'active' : '' }}" href="{{ route('admin.prep-sheet') }}">
                    <span class="link-inner"><i class="bi bi-card-checklist" aria-hidden="true"></i> Daily Prep Sheet</span>
                </a>
                <a class="nav-item-link {{ request()->routeIs('admin.availability.*') ? 'active' : '' }}" href="{{ route('admin.availability.index') }}">
                    <span class="link-inner"><i class="bi bi-calendar-week" aria-hidden="true"></i> Order Calendar</span>
                </a>
                <div class="nav-header">Store Catalog</div>
                <a class="nav-item-link {{ request()->routeIs('admin.menu.*', 'admin.products.*', 'admin.packages.*') ? 'active' : '' }}" href="{{ route('admin.menu.index') }}">
                    <span class="link-inner"><i class="bi bi-journal-richtext" aria-hidden="true"></i> Menu</span>
                </a>

                <div class="nav-header">Reports &amp; Feedback</div>
                <a class="nav-item-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}">
                    <span class="link-inner"><i class="bi bi-graph-up" aria-hidden="true"></i> Sales Reports</span>
                </a>
                <a class="nav-item-link {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}" href="{{ route('admin.reviews.index') }}">
                    <span class="link-inner"><i class="bi bi-chat-left-heart" aria-hidden="true"></i> Reviews</span>
                </a>
                <a class="nav-item-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}" href="{{ route('admin.notifications.index') }}">
                    <span class="link-inner"><i class="bi bi-bell" aria-hidden="true"></i> Notifications</span>
                    @php($unreadCount = auth()->user()->notifications()->where('is_read', false)->count())
                    @if($unreadCount > 0)
                        <span class="badge bg-danger rounded-pill px-2">{{ $unreadCount }}<span class="visually-hidden"> unread</span></span>
                    @endif
                </a>

                @if(auth()->user()->role->role_name === 'Owner')
                    <div class="nav-header">Administration</div>
                    <a class="nav-item-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                        <span class="link-inner"><i class="bi bi-people" aria-hidden="true"></i> Staff &amp; Users</span>
                    </a>
                @endif

                <hr class="border-secondary my-3 border-opacity-25">

                <a class="nav-item-link" href="{{ route('home') }}">
                    <span class="link-inner"><i class="bi bi-arrow-left-circle" aria-hidden="true"></i> Public Storefront</span>
                </a>
            </nav>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-outline-dark d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar" aria-label="Open menu">
                    <i class="bi bi-list fs-5" aria-hidden="true"></i>
                </button>
                <div class="fw-semibold text-dark d-none d-sm-block">
                    <span class="badge bg-danger-subtle text-danger me-2">{{ auth()->user()->role->role_name }}</span>
                    JQ Food Hub Management
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('admin.notifications.index') }}" class="btn btn-light position-relative border rounded-circle p-2" aria-label="Notifications{{ $unreadCount > 0 ? ', '.$unreadCount.' unread' : '' }}">
                    <i class="bi bi-bell text-secondary" aria-hidden="true"></i>
                    @if($unreadCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle" aria-hidden="true"></span>
                    @endif
                </a>

                <div class="d-flex align-items-center gap-2 ps-2 border-start">
                    <div class="rounded-circle bg-dark text-white fw-bold d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-size: 0.9rem;" aria-hidden="true">
                        {{ strtoupper(substr(auth()->user()->full_name, 0, 1)) }}
                    </div>
                    <div class="d-none d-md-block text-start">
                        <div class="fw-bold text-dark small leading-tight">{{ auth()->user()->full_name }}</div>
                        <div class="text-muted" style="font-size: 0.72rem;">{{ auth()->user()->email }}</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="ms-1">
                    @csrf
                    <button class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1" type="submit" aria-label="Log out">
                        <i class="bi bi-box-arrow-right" aria-hidden="true"></i> <span class="d-none d-sm-inline">Logout</span>
                    </button>
                </form>
            </div>
        </header>

        <main id="main" class="p-3 p-md-4 flex-grow-1">
            @include('partials.flash')

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-exclamation-triangle-fill text-danger fs-5" aria-hidden="true"></i>
                        <span class="fw-bold">Please check the form for errors:</span>
                    </div>
                    <ul class="mb-0 ps-3 small">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

@include('partials.confirm-modal')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
