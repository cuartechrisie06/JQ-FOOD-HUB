<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - JQ Food Hub</title>
    <meta name="robots" content="noindex">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Deliberately standalone (no Vite, no session, no auth) so an error page can never fail itself. --}}
    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; background: #fffdf5; color: #171717; }
        .bar { background: #f7c948; border-bottom: 2px solid #e0b034; }
        .brand { display: inline-flex; align-items: center; gap: 10px; color: #171717; font-weight: 800; text-decoration: none; }
        .brand img { width: 40px; height: 40px; border-radius: 50%; background: #fff; border: 1.5px solid #171717; padding: 2px; }
        .code { font-size: clamp(4rem, 14vw, 7rem); font-weight: 800; line-height: 1; color: #b42318; }
        .panel { max-width: 34rem; border: 1px solid #edece6; border-top: 6px solid #f7c948; border-radius: 12px; background: #fff; box-shadow: 0 6px 18px rgba(0,0,0,.08); }
        .btn-red { background: #b42318; border-color: #b42318; color: #fff; font-weight: 700; }
        .btn-red:hover { background: #8f1b13; border-color: #8f1b13; color: #fff; }
        a:focus-visible, button:focus-visible { outline: 3px solid #b42318; outline-offset: 2px; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
<header class="bar py-2">
    <div class="container">
        <a class="brand" href="{{ url('/') }}"><img src="{{ asset('images/logo.png') }}" alt="" width="40" height="40"> JQ Food Hub</a>
    </div>
</header>
<main class="container flex-grow-1 d-flex align-items-center justify-content-center py-5">
    <section class="panel text-center p-4 p-md-5">
        <p class="code mb-2">@yield('code')</p>
        <h1 class="h3 fw-bold">@yield('heading')</h1>
        <p class="text-secondary mb-4">@yield('message')</p>
        <div class="d-flex flex-wrap gap-2 justify-content-center">
            <a class="btn btn-red px-4" href="{{ url('/') }}">Back to home</a>
            <a class="btn btn-outline-dark px-4" href="{{ url('/products') }}">Browse products</a>
        </div>
    </section>
</main>
</body>
</html>
