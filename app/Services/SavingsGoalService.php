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

        return $query->get();
    }

    public function createSavingsGoal(array $data): SavingsGoal
    {
        return SavingsGoal::create(array_merge($data, [
            'user_id' => Auth::id(),
            'current_amount' => 0,
        ]));
    }

    public function updateSavingsGoal(SavingsGoal $goal, array $data): bool
    {
        return $goal->update($data);
    }

    public function addFunds(SavingsGoal $goal, float $amount): bool
    {
        return $goal->increment('current_amount', $amount);
    }

    public function deleteSavingsGoal(SavingsGoal $goal): bool
    {
        return $goal->delete();
    }
}
