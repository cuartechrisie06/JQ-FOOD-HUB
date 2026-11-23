<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        return view('admin.users', [
            'users' => User::with('role')->orderBy('full_name')->paginate(20),
            'roles' => Role::whereIn('role_name', ['Customer', 'Admin'])->orderBy('role_name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $adminRole = Role::where('role_name', 'Admin')->firstOrFail();

        User::create([
            'role_id' => $adminRole->role_id,
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password_hash' => Hash::make($validated['password']),
            'address' => null,
            'customer_type' => null,
            'business_name' => null,
            'status' => 'Active',
            'created_at' => now(),
        ]);

        return back()->with('status', 'Admin account created.');
    }

    public function updateStatus(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
        ]);

        if ($user->role->role_name === 'Owner') {
            return back()->withErrors(['status' => 'The Owner account cannot be deactivated.']);
        }

        $user->update(['status' => $validated['status']]);

        return back()->with('status', 'Account status updated.');
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role_id' => ['required', 'integer', Rule::exists('roles', 'role_id')],
        ]);

        $role = Role::findOrFail($validated['role_id']);

        if ($user->role->role_name === 'Owner' || $role->role_name === 'Owner') {
            return back()->withErrors(['role_id' => 'The Owner account and Owner role cannot be changed here.']);
        }

        if (! in_array($role->role_name, ['Customer', 'Admin'], true)) {
            return back()->withErrors(['role_id' => 'Only Customer and Admin roles are available.']);
        }

        $user->update(['role_id' => $role->role_id]);

        return back()->with('status', 'User role updated.');
    }
}
