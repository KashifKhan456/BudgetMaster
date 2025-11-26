<?php

namespace App\Services;

use App\Models\SavingsGoal;
use Illuminate\Support\Facades\Auth;

class SavingsGoalService
{
    public function getSavingsGoals(int $userId, array $filters = [])
    {
        $query = SavingsGoal::where('user_id', $userId);

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        return $query->paginate(10)->withQueryString();
    }

    public function createSavingsGoal(array $data): SavingsGoal
    {
        if (SavingsGoal::where('user_id', Auth::id())->where('name', $data['name'])->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['name' => 'A goal with this name already exists.']);
        }

        return SavingsGoal::create(array_merge($data, [
            'user_id' => Auth::id(),
            'current_amount' => 0,
        ]));
    }

    public function updateSavingsGoal(SavingsGoal $goal, array $data): bool
    {
        // Check for duplicate name if name is being changed
        if (isset($data['name']) && $data['name'] !== $goal->name) {
             if (SavingsGoal::where('user_id', Auth::id())->where('name', $data['name'])->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages(['name' => 'A goal with this name already exists.']);
            }
        }
        return $goal->update($data);
    }

    public function addFunds(SavingsGoal $goal, float $amount): bool
    {
        if ($goal->current_amount >= $goal->target_amount) {
            throw \Illuminate\Validation\ValidationException::withMessages(['add_amount' => 'Goal is already reached.']);
        }
        
        if ($goal->current_amount + $amount > $goal->target_amount) {
             throw \Illuminate\Validation\ValidationException::withMessages(['add_amount' => 'Amount exceeds the remaining target. You only need ' . ($goal->target_amount - $goal->current_amount)]);
        }

        return $goal->increment('current_amount', $amount);
    }

    public function deleteSavingsGoal(SavingsGoal $goal): bool
    {
        return $goal->delete();
    }
}
