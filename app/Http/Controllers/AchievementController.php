<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class AchievementController extends Controller
{
    public function index()
    {
        $achievements = Achievement::all();
        $userAchievements = Auth::user()->achievements()->pluck('achievement_id')->toArray();

        return Inertia::render('Achievements/Index', [
            'achievements' => $achievements,
            'userAchievements' => $userAchievements,
        ]);
    }
}
