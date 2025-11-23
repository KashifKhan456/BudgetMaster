<?php

namespace App\Http\Controllers;

use App\Models\RecurringTransaction;
use App\Models\Category;
use App\Http\Requests\StoreRecurringTransactionRequest;
use App\Http\Requests\UpdateRecurringTransactionRequest;
use App\Services\RecurringTransactionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class RecurringTransactionController extends Controller
{
    protected $recurringTransactionService;

    public function __construct(RecurringTransactionService $recurringTransactionService)
    {
        $this->recurringTransactionService = $recurringTransactionService;
    }

    public function index(Request $request)
    {
        return Inertia::render('Recurring/Index', [
            'recurringTransactions' => $this->recurringTransactionService->getRecurringTransactions(Auth::id(), $request->only(['search', 'category_id', 'type'])),
            'categories' => Category::where('user_id', Auth::id())->get(),
            'filters' => $request->only(['search', 'category_id', 'type']),
        ]);
    }

    public function store(StoreRecurringTransactionRequest $request)
    {
        $this->recurringTransactionService->createRecurringTransaction($request->validated());

        return redirect()->back()->with('success', 'Recurring transaction created successfully.');
    }

    public function update(UpdateRecurringTransactionRequest $request, RecurringTransaction $recurring)
    {
        $this->recurringTransactionService->updateRecurringTransaction($recurring, $request->validated());

        return redirect()->back()->with('success', 'Recurring transaction updated successfully.');
    }

    public function destroy(RecurringTransaction $recurring)
    {
        if ($recurring->user_id !== Auth::id()) {
            abort(403);
        }

        $this->recurringTransactionService->deleteRecurringTransaction($recurring);

        return redirect()->back()->with('success', 'Recurring transaction deleted successfully.');
    }
}
