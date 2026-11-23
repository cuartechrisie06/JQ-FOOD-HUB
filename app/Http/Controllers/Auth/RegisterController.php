<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $customerRole = Role::where('role_name', 'Customer')->firstOrFail();

        $user = User::create([
            'role_id' => $customerRole->role_id,
            'full_name' => $request->validated('full_name'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'password_hash' => Hash::make($request->validated('password')),
            'address' => null,
            'customer_type' => $request->validated('customer_type'),
            'business_name' => $request->validated('customer_type') === 'Business'
                ? $request->validated('business_name')
                : null,
            'status' => 'Active',
            'created_at' => now(),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home');
    }
}
