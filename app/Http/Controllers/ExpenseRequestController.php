<?php

namespace App\Http\Controllers;

use App\Models\ExpenseRequest;
use App\Models\Budget;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ExpenseRequestController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // If user is a parent (owner of budgets), show requests for those budgets
        // If user is a child (member), show their own requests
        
        $requests = ExpenseRequest::with(['user', 'budget', 'category'])
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id) // My requests
                      ->orWhereHas('budget', function ($q) use ($user) {
                          $q->where('user_id', $user->id); // Requests on my budgets
                      });
            })
            ->latest()
            ->get();

        $ownedBudgets = $user->budgets()->get();
        $sharedBudgets = $user->sharedBudgets()->get();
        $budgets = $ownedBudgets->merge($sharedBudgets);
        $categories = \App\Models\Category::all();

        return Inertia::render('ExpenseRequests/Index', [
            'requests' => $requests,
            'budgets' => $budgets,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'budget_id' => 'required|exists:budgets,id',
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);

        $request->user()->expenseRequests()->create($validated);

        return redirect()->back()->with('success', 'Expense request submitted.');
    }

    public function update(Request $request, ExpenseRequest $expenseRequest)
    {
        $user = Auth::user();
        
        // Only the budget owner can approve/reject
        if ($expenseRequest->budget->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'rejection_reason' => 'nullable|string|required_if:status,rejected',
        ]);

        DB::transaction(function () use ($expenseRequest, $validated) {
            $expenseRequest->update([
                'status' => $validated['status'],
                'rejection_reason' => $validated['rejection_reason'] ?? null,
            ]);

            if ($validated['status'] === 'approved') {
                // Create the actual transaction
                Transaction::create([
                    'user_id' => $expenseRequest->user_id, // The child spent the money
                    'category_id' => $expenseRequest->category_id,
                    'amount' => $expenseRequest->amount,
                    'type' => 'expense',
                    'date' => now(),
                    'description' => $expenseRequest->description ?? 'Approved Expense Request',
                ]);
            }
        });

        return redirect()->back()->with('success', 'Request ' . $validated['status'] . '.');
    }
}
