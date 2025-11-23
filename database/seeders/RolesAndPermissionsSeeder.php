<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::findOrCreate('edit articles');
        Permission::findOrCreate('delete articles');
        Permission::findOrCreate('publish articles');
        Permission::findOrCreate('unpublish articles');
        Permission::findOrCreate('manage users');

        // create roles and assign created permissions

        // this can be done as separate statements
        $role = Role::findOrCreate('writer');
        $role->givePermissionTo('edit articles');

        // or may be done by chaining
        $role = Role::findOrCreate('moderator')
            ->givePermissionTo(['publish articles', 'unpublish articles']);

        $role = Role::findOrCreate('super-admin');
        $role->givePermissionTo(Permission::all());
    }
}
