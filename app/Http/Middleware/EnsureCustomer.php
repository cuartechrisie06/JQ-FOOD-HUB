<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomer
{
    public function handle(Request $request, Closure $next): Response
    {
        $role = $request->user()?->role?->role_name;

        return $role === 'Customer' ? $next($request) : redirect()->route('home');
    }
}
