<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class OwnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ownerRole = Role::where('role_name', 'Owner')->firstOrFail();

        $email = env('OWNER_EMAIL') ?: (env('JQ_OWNER_EMAIL') ?: config('jqfood.owner_email', 'owner@jqfoodhub.local'));
        $name = env('OWNER_NAME') ?: (env('JQ_OWNER_NAME') ?: config('jqfood.owner_name', 'JQ Food Hub Owner'));
        $password = env('OWNER_PASSWORD') ?: (env('JQ_OWNER_PASSWORD') ?: config('jqfood.owner_password'));
        $phone = env('OWNER_PHONE') ?: (env('JQ_OWNER_PHONE') ?: config('jqfood.owner_phone', ''));
        $address = env('OWNER_ADDRESS') ?: (env('JQ_OWNER_ADDRESS') ?: config('jqfood.owner_address', 'Villa Patricia Subd., Mankilam, Tagum City'));

        if (empty($password)) {
            throw new RuntimeException('OWNER_PASSWORD must be configured in .env before seeding.');
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'role_id' => $ownerRole->role_id,
                'full_name' => $name,
                'phone' => $phone,
                'password_hash' => Hash::make($password),
                'address' => $address,
                'customer_type' => 'Individual',
                'business_name' => null,
                'status' => 'Active',
                'created_at' => now(),
            ]
        );
    }
}
