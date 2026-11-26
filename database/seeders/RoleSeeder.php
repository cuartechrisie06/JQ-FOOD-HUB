<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['role_name' => 'Customer', 'description' => 'Can browse, order, book, and manage their own account.'],
            ['role_name' => 'Admin', 'description' => 'Can manage catalog, orders, payments, and bookings.'],
            ['role_name' => 'Owner', 'description' => 'Has full access and manages staff accounts.'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['role_name' => $role['role_name']], $role);
        }
    }
}
