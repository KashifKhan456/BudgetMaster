<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Category;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'category_id', 'type', 'search']);

        return Inertia::render('Transactions/Index', [
            'transactions' => $this->transactionService->getTransactions(Auth::id(), $filters),
            'categories' => Category::where('user_id', Auth::id())->get(),
            'filters' => $filters,
        ]);
    }

    public function store(StoreTransactionRequest $request)
    {
        $this->transactionService->createTransaction($request->validated());

        return redirect()->back()->with('success', 'Transaction created successfully.');
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        $this->transactionService->updateTransaction($transaction, $request->validated());

        return redirect()->back()->with('success', 'Transaction updated successfully.');
    }

    public function destroy(Transaction $transaction)
    {
        if ($transaction->user_id !== Auth::id()) {
            abort(403);
        }

        $this->transactionService->deleteTransaction($transaction);

        return redirect()->back()->with('success', 'Transaction deleted successfully.');
    }
}
