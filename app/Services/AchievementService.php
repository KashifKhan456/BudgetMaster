<?php

namespace App\Services;

use App\Models\User;
use App\Models\Achievement;

class AchievementService
{
    public function checkAndUnlock(User $user, string $type)
    {
        $achievements = Achievement::where('criteria_type', $type)->get();

        foreach ($achievements as $achievement) {
            if ($user->achievements()->where('achievement_id', $achievement->id)->exists()) {
                continue;
            }

            if ($this->checkCriteria($user, $achievement)) {
                $user->achievements()->attach($achievement->id);
                session()->flash('achievement_unlocked', $achievement->name);
            }
        }
    }

    protected function checkCriteria(User $user, Achievement $achievement)
    {
        switch ($achievement->criteria_type) {
            case 'transaction_count':
                return $user->transactions()->count() >= $achievement->criteria_value;
            case 'budget_created':
                return $user->budgets()->count() >= $achievement->criteria_value;
            case 'goal_created':
                return $user->savingsGoals()->count() >= $achievement->criteria_value;
            default:
                return false;
        }
    }
}
