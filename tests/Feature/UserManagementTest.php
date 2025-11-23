<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles and permissions
        $role = Role::create(['name' => 'super-admin']);
        $permission = Permission::create(['name' => 'manage users']);
        $role->givePermissionTo($permission);
    }

    public function test_super_admin_can_access_user_management()
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $response = $this->actingAs($admin)->get(route('users.index'));

        $response->assertStatus(200);
    }

    public function test_regular_user_cannot_access_user_management()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('users.index'));

        $response->assertStatus(403);
    }

    public function test_super_admin_can_create_user()
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'writer',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
        $this->assertTrue(User::where('email', 'new@example.com')->first()->hasRole('writer'));
    }

    public function test_super_admin_can_update_user()
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $user = User::factory()->create();
        $role = Role::create(['name' => 'writer']);
        $user->assignRole('writer');

        $response = $this->actingAs($admin)->put(route('users.update', $user->id), [
            'name' => 'Updated Name',
            'email' => $user->email,
            'role' => 'writer',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated Name']);
    }

    public function test_super_admin_can_delete_user()
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $user = User::factory()->create();

        $response = $this->actingAs($admin)->delete(route('users.destroy', $user->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
