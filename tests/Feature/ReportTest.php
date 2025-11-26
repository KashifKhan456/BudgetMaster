<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_page_displays_advanced_analytics()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);
        
        // Create Budget
        Budget::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'amount' => 1000,
            'period' => 'month',
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
        ]);

        // Create Transactions
        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'amount' => 200,
            'type' => 'expense',
            'date' => now(),
            'description' => 'Grocery Shopping',
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'amount' => 2000,
            'type' => 'income',
            'date' => now(),
            'description' => 'Salary',
        ]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertStatus(200);
        
        // Check for new props
        $response->assertInertia(fn ($page) => $page
            ->component('Reports/Index')
            ->has('budgetVsActual')
            ->has('savingsRate')
            ->has('topSpenders')
            ->has('yoyGrowth')
            ->has('netCashFlow')
            ->has('recurringVsDiscretionary')
            ->where('savingsRate', 90) // (2000 - 200) / 2000 = 0.9 -> 90%
            ->where('netCashFlow', 1800) // 2000 - 200
        );
    }

    public function test_export_functionality_returns_csv()
    {
        $user = User::factory()->create();
        Transaction::factory()->create([
            'user_id' => $user->id,
            'amount' => 100,
            'type' => 'expense',
            'date' => now(),
            'description' => 'Test Expense',
        ]);

        $response = $this->actingAs($user)->get(route('reports.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename=transactions_export.csv');
        
        $content = $response->streamedContent();
        $this->assertStringContainsString('Test Expense', $content);
        $this->assertStringContainsString('100', $content);
    }
}
