<?php

namespace App\Http\Controllers;

use App\Models\SavingsGoal;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class SavingsGoalController extends Controller
{
    public function index()
    {
        return Inertia::render('Goals/Index', [
            'goals' => SavingsGoal::where('user_id', Auth::id())->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:0',
            'target_date' => 'required|date|after:today',
        ]);

        SavingsGoal::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'target_amount' => $request->target_amount,
            'current_amount' => 0,
            'target_date' => $request->target_date,
        ]);

        return redirect()->back()->with('success', 'Savings goal created successfully.');
    }

    public function update(Request $request, SavingsGoal $goal)
    {
        // Check if we are just adding funds
        if ($request->has('add_amount')) {
             $request->validate([
                'add_amount' => 'required|numeric|min:0',
            ]);
            
            if ($goal->user_id !== Auth::id()) {
                abort(403);
            }

            $goal->increment('current_amount', $request->add_amount);
            return redirect()->back()->with('success', 'Funds added successfully.');
        }

        if ($goal->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:0',
            'target_date' => 'required|date',
        ]);

        $goal->update($request->only(['name', 'target_amount', 'target_date']));

        return redirect()->back()->with('success', 'Savings goal updated successfully.');
    }

    public function destroy(SavingsGoal $goal)
    {
        if ($goal->user_id !== Auth::id()) {
            abort(403);
        }

        $goal->delete();

        return redirect()->back()->with('success', 'Savings goal deleted successfully.');
    }
}
