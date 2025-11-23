<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

$user = User::first();

if (!$user) {
    echo "No users found. Creating a default admin user...\n";
    $user = User::create([
        'name' => 'Super Admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
    ]);
}

$user->assignRole('super-admin');

echo "Assigned 'super-admin' role to user: " . $user->email . "\n";
