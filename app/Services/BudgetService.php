<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class BudgetService
{
    public function getBudgets(int $userId, array $filters = [])
    {
        $query = Budget::with(['category', 'users'])
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereHas('users', function ($q2) use ($userId) {
                      $q2->where('users.id', $userId);
                  });
            });

        if (!empty($filters['search'])) {
            $query->whereHas('category', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['start_date'])) {
            // Show budgets that end after or on the filter start date (Overlap logic)
            $query->where('end_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            // Show budgets that start before or on the filter end date (Overlap logic)
            $query->where('start_date', '<=', $filters['end_date']);
        }

        return $query->get();
    }

    public function shareBudget(int $budgetId, string $email)
    {
        $budget = Budget::findOrFail($budgetId);
        $user = User::where('email', $email)->firstOrFail();

        if ($budget->user_id === $user->id) {
            throw new \Exception("Cannot share budget with yourself.");
        }

        if (!$budget->users()->where('users.id', $user->id)->exists()) {
            $budget->users()->attach($user->id, ['role' => 'member']);
        }
    }

    public function removeUser(int $budgetId, int $userId)
    {
        $budget = Budget::findOrFail($budgetId);
        $budget->users()->detach($userId);
    }

    public function createBudget(array $data): Budget
    {
        return Budget::create(array_merge($data, ['user_id' => Auth::id()]));
    }

    public function updateBudget(Budget $budget, array $data): bool
    {
        return $budget->update($data);
    }

    public function deleteBudget(Budget $budget): bool
    {
        return $budget->delete();
    }
}
