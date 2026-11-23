<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $ownerPassword = config('jqfood.owner_password');
        if (app()->environment('production') && (! is_string($ownerPassword) || strlen($ownerPassword) < 12)) {
            throw new RuntimeException('Set JQ_OWNER_PASSWORD to a unique password of at least 12 characters before production seeding.');
        }

        $roles = [
            ['role_name' => 'Customer', 'description' => 'Can browse, order, book, and manage their own account.'],
            ['role_name' => 'Admin', 'description' => 'Can manage catalog, orders, payments, and bookings.'],
            ['role_name' => 'Owner', 'description' => 'Has full access and manages staff accounts.'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['role_name' => $role['role_name']], $role);
        }

        $ownerRole = Role::where('role_name', 'Owner')->firstOrFail();

        User::updateOrCreate(
            ['email' => config('jqfood.owner_email')],
            [
                'role_id' => $ownerRole->role_id,
                'full_name' => config('jqfood.owner_name'),
                'phone' => config('jqfood.owner_phone'),
                'password_hash' => Hash::make($ownerPassword ?: 'password'),
                'address' => config('jqfood.owner_address'),
                'customer_type' => 'Individual',
                'business_name' => null,
                'status' => 'Active',
                'created_at' => now(),
            ]
        );
    }
}
