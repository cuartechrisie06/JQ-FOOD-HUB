<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $role = $request->user()?->role?->role_name;

        return in_array($role, ['Admin', 'Owner'], true)
            ? $next($request)
            : redirect()->route('home');
    }
}
