<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Achievement;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AchievementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\AchievementSeeder::class);
    }

    public function test_unlocks_first_transaction_achievement()
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Test Category', 'user_id' => $user->id, 'type' => 'expense']);

        $response = $this->actingAs($user)->post(route('transactions.store'), [
            'amount' => 100,
            'description' => 'Test Transaction',
            'date' => now()->toDateString(),
            'category_id' => $category->id,
            'type' => 'expense',
        ]);

        $response->assertSessionHas('achievement_unlocked', 'First Transaction');
        $this->assertTrue($user->achievements()->where('name', 'First Transaction')->exists());
    }

    public function test_unlocks_budget_master_achievement()
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Test Category', 'user_id' => $user->id, 'type' => 'expense']);

        $response = $this->actingAs($user)->post(route('budgets.store'), [
            'category_id' => $category->id,
            'amount' => 500,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'period' => 'month',
        ]);

        $response->assertSessionHas('achievement_unlocked', 'Budget Master');
        $this->assertTrue($user->achievements()->where('name', 'Budget Master')->exists());
    }

    public function test_unlocks_goal_setter_achievement()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('goals.store'), [
            'name' => 'New Car',
            'target_amount' => 20000,
            'target_date' => now()->addYear()->toDateString(),
        ]);

        $response->assertSessionHas('achievement_unlocked', 'Goal Setter');
        $this->assertTrue($user->achievements()->where('name', 'Goal Setter')->exists());
    }
}
