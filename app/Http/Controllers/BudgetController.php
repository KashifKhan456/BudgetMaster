<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Http\Requests\StoreBudgetRequest;
use App\Http\Requests\UpdateBudgetRequest;
use App\Services\BudgetService;
use App\Services\AchievementService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    protected $budgetService;
    protected $achievementService;

    public function __construct(BudgetService $budgetService, AchievementService $achievementService)
    {
        $this->budgetService = $budgetService;
        $this->achievementService = $achievementService;
    }

    public function index(Request $request)
    {
        return Inertia::render('Budgets/Index', [
            'budgets' => $this->budgetService->getBudgets(Auth::id(), $request->only(['search', 'category_id', 'start_date', 'end_date'])),
            'categories' => Category::where('user_id', Auth::id())->get(),
            'filters' => $request->only(['search', 'category_id', 'start_date', 'end_date']),
        ]);
    }

    public function store(StoreBudgetRequest $request)
    {
        $this->budgetService->createBudget($request->validated());
        $this->achievementService->checkAndUnlock(Auth::user(), 'budget_created');

        return redirect()->back()->with('success', 'Budget created successfully.');
    }

    public function update(UpdateBudgetRequest $request, Budget $budget)
    {
        $this->budgetService->updateBudget($budget, $request->validated());

        return redirect()->back()->with('success', 'Budget updated successfully.');
    }

    public function destroy(Budget $budget)
    {
        if ($budget->user_id !== Auth::id()) {
            abort(403);
        }
        $this->budgetService->deleteBudget($budget);
        return redirect()->route('budgets.index');
    }

    public function share(Request $request, Budget $budget)
    {
        if ($budget->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        try {
            $this->budgetService->shareBudget($budget->id, $request->email);
            return redirect()->back()->with('success', 'Budget shared successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['email' => $e->getMessage()]);
        }
    }

    public function unshare(Budget $budget, User $user)
    {
        if ($budget->user_id !== Auth::id()) {
            abort(403);
        }

        $this->budgetService->removeUser($budget->id, $user->id);
        return redirect()->back()->with('success', 'User removed from budget.');
    }
}
