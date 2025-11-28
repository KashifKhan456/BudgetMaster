<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Achievement;

class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        $achievements = [
            [
                'name' => 'First Transaction',
                'description' => 'Create your first transaction.',
                'icon' => 'fas fa-receipt',
                'criteria_type' => 'transaction_count',
                'criteria_value' => 1,
            ],
            [
                'name' => 'Budget Master',
                'description' => 'Create your first budget.',
                'icon' => 'fas fa-wallet',
                'criteria_type' => 'budget_created',
                'criteria_value' => 1,
            ],
            [
                'name' => 'Goal Setter',
                'description' => 'Create a savings goal.',
                'icon' => 'fas fa-bullseye',
                'criteria_type' => 'goal_created',
                'criteria_value' => 1,
            ],
            [
                'name' => 'Super Saver',
                'description' => 'Reach 10 transactions.',
                'icon' => 'fas fa-star',
                'criteria_type' => 'transaction_count',
                'criteria_value' => 10,
            ],
        ];

        foreach ($achievements as $achievement) {
            Achievement::firstOrCreate(
                ['name' => $achievement['name']],
                $achievement
            );
        }
    }
}
