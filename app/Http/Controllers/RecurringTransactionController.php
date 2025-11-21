<?php

namespace App\Http\Controllers;

use App\Models\RecurringTransaction;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class RecurringTransactionController extends Controller
{
    public function index()
    {
        return Inertia::render('Recurring/Index', [
            'recurringTransactions' => RecurringTransaction::where('user_id', Auth::id())
                ->with('category')
                ->get(),
            'categories' => Category::where('user_id', Auth::id())->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:expense,income',
            'interval' => 'required|in:daily,weekly,monthly,yearly',
            'start_date' => 'required|date',
            'description' => 'nullable|string|max:255',
        ]);

        RecurringTransaction::create([
            'user_id' => Auth::id(),
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'type' => $request->type,
            'interval' => $request->interval,
            'start_date' => $request->start_date,
            'next_run_date' => $request->start_date, // Initial run date
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'Recurring transaction created successfully.');
    }

    public function update(Request $request, RecurringTransaction $recurring)
    {
        if ($recurring->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:expense,income',
            'interval' => 'required|in:daily,weekly,monthly,yearly',
            'start_date' => 'required|date',
            'description' => 'nullable|string|max:255',
        ]);

        $recurring->update($request->only(['category_id', 'amount', 'type', 'interval', 'start_date', 'description']));

        return redirect()->back()->with('success', 'Recurring transaction updated successfully.');
    }

    public function destroy(RecurringTransaction $recurring)
    {
        if ($recurring->user_id !== Auth::id()) {
            abort(403);
        }

        $recurring->delete();

        return redirect()->back()->with('success', 'Recurring transaction deleted successfully.');
    }
}
