<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CustomerAccountController extends Controller
{
    public function edit(): View
    {
        return view('account.edit', ['user' => auth()->user()]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if ($data['customer_type'] !== 'Business') {
            $data['business_name'] = null;
        }

        $request->user()->update($data);

        return redirect()->route('account.edit')->with('status', 'Profile updated.');
    }
}
