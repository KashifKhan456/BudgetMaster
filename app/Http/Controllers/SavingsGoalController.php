<?php

namespace App\Http\Controllers;

use App\Models\SavingsGoal;
use App\Http\Requests\StoreSavingsGoalRequest;
use App\Http\Requests\UpdateSavingsGoalRequest;
use App\Services\SavingsGoalService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class SavingsGoalController extends Controller
{
    protected $savingsGoalService;

    public function __construct(SavingsGoalService $savingsGoalService)
    {
        $this->savingsGoalService = $savingsGoalService;
    }

    public function index(Request $request)
    {
        return Inertia::render('Goals/Index', [
            'goals' => $this->savingsGoalService->getSavingsGoals(Auth::id(), $request->only('search')),
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(StoreSavingsGoalRequest $request)
    {
        $this->savingsGoalService->createSavingsGoal($request->validated());

        return redirect()->back()->with('success', 'Savings goal created successfully.');
    }

    public function update(Request $request, SavingsGoal $goal)
    {
        // Check if we are just adding funds
        if ($request->has('add_amount')) {
             // Manually validate or use a separate request if we split the route
             $request->validate([
                'add_amount' => 'required|numeric|min:0',
            ]);
            
            if ($goal->user_id !== Auth::id()) {
                abort(403);
            }

            $this->savingsGoalService->addFunds($goal, $request->add_amount);
            return redirect()->back()->with('success', 'Funds added successfully.');
        }

        // For standard update, we can use the Form Request manually or rely on validation here
        // Since we are sharing the update method, we can't type hint UpdateSavingsGoalRequest directly without conditional logic
        // So we will manually validate using the rules from the request or just validate here.
        // To be cleaner, let's just validate here for now as splitting the route is out of scope for this refactor
        
        if ($goal->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:0',
            'target_date' => 'required|date',
        ]);

        $this->savingsGoalService->updateSavingsGoal($goal, $validated);

        return redirect()->back()->with('success', 'Savings goal updated successfully.');
    }

    public function destroy(SavingsGoal $goal)
    {
        if ($goal->user_id !== Auth::id()) {
            abort(403);
        }

        $this->savingsGoalService->deleteSavingsGoal($goal);

        return redirect()->back()->with('success', 'Savings goal deleted successfully.');
    }
}
