<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(Request $request): View
    {
        // Remember where a guest came from (e.g. a product page) so login
        // returns them there instead of the home page.
        $previous = url()->previous();
        $isAuthPage = (bool) preg_match('#/(login|register|forgot-password|reset-password)#', $previous);

        if (! $request->session()->has('url.intended')
            && str_starts_with($previous, url('/'))
            && rtrim($previous, '/') !== rtrim(url('/'), '/')
            && ! $isAuthPage) {
            $request->session()->put('url.intended', $previous);
        }

        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || $user->status !== 'Active' || ! Hash::check($credentials['password'], $user->password_hash)) {
            return back()->withErrors(['email' => 'The provided credentials are invalid or the account is inactive.'])
                ->onlyInput('email');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if (in_array($user->role->role_name, ['Admin', 'Owner'], true)) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('home'));
    }

    public function destroy(): RedirectResponse
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('home');
    }
}
