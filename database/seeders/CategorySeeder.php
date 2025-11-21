<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();

        if (!$user) {
            return;
        }

        $categories = [
            ['name' => 'Salary', 'type' => 'income', 'color' => 'green'],
            ['name' => 'Freelance', 'type' => 'income', 'color' => 'blue'],
            ['name' => 'Food', 'type' => 'expense', 'color' => 'orange'],
            ['name' => 'Rent', 'type' => 'expense', 'color' => 'red'],
            ['name' => 'Utilities', 'type' => 'expense', 'color' => 'yellow'],
            ['name' => 'Entertainment', 'type' => 'expense', 'color' => 'purple'],
            ['name' => 'Transport', 'type' => 'expense', 'color' => 'gray'],
        ];

        foreach ($categories as $category) {
            Category::create([
                'user_id' => $user->id,
                'name' => $category['name'],
                'type' => $category['type'],
                'color' => $category['color'],
            ]);
        }
    }
}
