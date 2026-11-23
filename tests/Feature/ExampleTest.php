<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_database_seeds_roles_and_owner_account(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(
            ['Customer', 'Admin', 'Owner'],
            Role::orderBy('role_id')->pluck('role_name')->all()
        );

        $owner = User::where('email', env('JQ_OWNER_EMAIL', 'owner@jqfoodhub.local'))->first();

        $this->assertNotNull($owner);
        $this->assertSame('Owner', $owner->role->role_name);
        $this->assertSame('Active', $owner->status);
        $this->assertTrue(password_verify(env('JQ_OWNER_PASSWORD', 'password'), $owner->password_hash));
    }

    public function test_customer_can_register(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->post('/register', [
            'full_name' => 'New Customer',
            'email' => 'customer@example.com',
            'phone' => '09171234567',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'customer_type' => 'Business',
            'business_name' => 'New Customer Store',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'email' => 'customer@example.com',
            'customer_type' => 'Business',
            'business_name' => 'New Customer Store',
        ]);
    }

    public function test_customer_login_redirects_home_and_admin_login_redirects_admin(): void
    {
        $this->seed(DatabaseSeeder::class);
        $customerRole = Role::where('role_name', 'Customer')->firstOrFail();
        $adminRole = Role::where('role_name', 'Admin')->firstOrFail();

        $customer = User::create([
            'role_id' => $customerRole->role_id,
            'full_name' => 'Customer Login',
            'email' => 'login-customer@example.com',
            'phone' => '09170000001',
            'password_hash' => Hash::make('password123'),
            'address' => null,
            'customer_type' => 'Individual',
            'business_name' => null,
            'status' => 'Active',
            'created_at' => now(),
        ]);

        $admin = User::create([
            'role_id' => $adminRole->role_id,
            'full_name' => 'Admin Login',
            'email' => 'login-admin@example.com',
            'phone' => '09170000002',
            'password_hash' => Hash::make('password123'),
            'address' => null,
            'customer_type' => null,
            'business_name' => null,
            'status' => 'Active',
            'created_at' => now(),
        ]);

        $this->post('/login', ['email' => $customer->email, 'password' => 'password123'])
            ->assertRedirect('/');
        $this->post('/logout');

        $this->post('/login', ['email' => $admin->email, 'password' => 'password123'])
            ->assertRedirect('/admin');
    }

    public function test_inactive_user_login_is_blocked(): void
    {
        $this->seed(DatabaseSeeder::class);
        $customerRole = Role::where('role_name', 'Customer')->firstOrFail();

        $user = User::create([
            'role_id' => $customerRole->role_id,
            'full_name' => 'Inactive Customer',
            'email' => 'inactive@example.com',
            'phone' => '09170000003',
            'password_hash' => Hash::make('password123'),
            'address' => null,
            'customer_type' => 'Individual',
            'business_name' => null,
            'status' => 'Inactive',
            'created_at' => now(),
        ]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password123'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_role_access_is_enforced(): void
    {
        $this->seed(DatabaseSeeder::class);
        $customerRole = Role::where('role_name', 'Customer')->firstOrFail();
        $adminRole = Role::where('role_name', 'Admin')->firstOrFail();
        $ownerRole = Role::where('role_name', 'Owner')->firstOrFail();

        $customer = User::create([
            'role_id' => $customerRole->role_id,
            'full_name' => 'Role Customer',
            'email' => 'role-customer@example.com',
            'phone' => '09170000004',
            'password_hash' => Hash::make('password123'),
            'address' => null,
            'customer_type' => 'Individual',
            'business_name' => null,
            'status' => 'Active',
            'created_at' => now(),
        ]);
        $admin = User::create([
            'role_id' => $adminRole->role_id,
            'full_name' => 'Role Admin',
            'email' => 'role-admin@example.com',
            'phone' => '09170000005',
            'password_hash' => Hash::make('password123'),
            'address' => null,
            'customer_type' => null,
            'business_name' => null,
            'status' => 'Active',
            'created_at' => now(),
        ]);
        $owner = User::where('role_id', $ownerRole->role_id)->firstOrFail();

        $this->actingAs($customer)->get('/admin')->assertRedirect('/');
        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/admin/users')->assertRedirect('/admin');
        $this->actingAs($owner)->get('/admin/users')->assertOk();
    }
}
