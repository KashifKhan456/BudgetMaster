<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Category;
use App\Models\ExpenseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ExpenseRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_child_can_create_expense_request()
    {
        $parent = User::factory()->create();
        $child = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $parent->id]);
        $budget = Budget::factory()->create(['user_id' => $parent->id]);

        // Share budget with child
        $budget->users()->attach($child->id, ['role' => 'member']);

        $response = $this->actingAs($child)->post(route('expense-requests.store'), [
            'budget_id' => $budget->id,
            'category_id' => $category->id,
            'amount' => 50,
            'description' => 'Movie ticket',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('expense_requests', [
            'user_id' => $child->id,
            'budget_id' => $budget->id,
            'amount' => 50,
            'status' => 'pending',
        ]);
    }

    public function test_parent_can_approve_request()
    {
        $parent = User::factory()->create();
        $child = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $parent->id]);
        $budget = Budget::factory()->create(['user_id' => $parent->id]);
        
        $request = ExpenseRequest::create([
            'user_id' => $child->id,
            'budget_id' => $budget->id,
            'category_id' => $category->id,
            'amount' => 50,
            'description' => 'Movie ticket',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($parent)->put(route('expense-requests.update', $request), [
            'status' => 'approved',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('expense_requests', [
            'id' => $request->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $child->id,
            'amount' => 50,
            'type' => 'expense',
            'description' => 'Movie ticket',
        ]);
    }

    public function test_parent_can_reject_request()
    {
        $parent = User::factory()->create();
        $child = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $parent->id]);
        $budget = Budget::factory()->create(['user_id' => $parent->id]);
        
        $request = ExpenseRequest::create([
            'user_id' => $child->id,
            'budget_id' => $budget->id,
            'category_id' => $category->id,
            'amount' => 50,
            'description' => 'Movie ticket',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($parent)->put(route('expense-requests.update', $request), [
            'status' => 'rejected',
            'rejection_reason' => 'Too expensive',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('expense_requests', [
            'id' => $request->id,
            'status' => 'rejected',
            'rejection_reason' => 'Too expensive',
        ]);

        $this->assertDatabaseMissing('transactions', [
            'description' => 'Movie ticket',
        ]);
    }
}
