<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolesAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_assign_role_to_user()
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'writer']);

        $user->assignRole('writer');

        $this->assertTrue($user->hasRole('writer'));
    }

    public function test_role_has_permission()
    {
        $role = Role::create(['name' => 'writer']);
        $permission = Permission::create(['name' => 'edit articles']);

        $role->givePermissionTo($permission);

        $this->assertTrue($role->hasPermissionTo('edit articles'));
    }

    public function test_user_with_role_has_permission()
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'writer']);
        $permission = Permission::create(['name' => 'edit articles']);

        $role->givePermissionTo($permission);
        $user->assignRole('writer');

        $this->assertTrue($user->can('edit articles'));
    }
}
