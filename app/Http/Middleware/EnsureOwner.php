<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        return $request->user()?->role?->role_name === 'Owner'
            ? $next($request)
            : redirect()->route('admin.dashboard');
    }
}
