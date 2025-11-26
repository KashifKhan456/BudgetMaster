<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class TransactionService
{
    public function getTransactions(int $userId, array $filters)
    {
        $query = Transaction::where('user_id', $userId)->with(['category', 'splits.category']);

        if (!empty($filters['start_date'])) {
            $query->whereDate('date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('date', '<=', $filters['end_date']);
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['search'])) {
            $query->where('description', 'like', '%' . $filters['search'] . '%');
        }

        return $query->latest('date')->paginate(10)->withQueryString();
    }

    public function createTransaction(array $data): Transaction
    {
        $transaction = Transaction::create(array_merge($data, ['user_id' => Auth::id()]));

        if (!empty($data['splits'])) {
            $transaction->splits()->createMany($data['splits']);
        }

        $this->checkBudgetAlerts($transaction);

        return $transaction;
    }

    public function updateTransaction(Transaction $transaction, array $data): bool
    {
        $updated = $transaction->update($data);

        if (!empty($data['splits'])) {
            // Delete existing splits and recreate them (easier than syncing for now)
            $transaction->splits()->delete();
            $transaction->splits()->createMany($data['splits']);
        } else {
            // If no splits provided (or empty array), remove existing splits
            // But only if we are explicitly handling splits in the UI. 
            // If the UI sends an empty array for splits, it means "no splits".
            if (isset($data['splits'])) {
                 $transaction->splits()->delete();
            }
        }

        $this->checkBudgetAlerts($transaction);

        return $updated;
    }

    public function deleteTransaction(Transaction $transaction): bool
    {
        return $transaction->delete();
    }

    protected function checkBudgetAlerts(Transaction $transaction)
    {
        $user = Auth::user();
        $date = $transaction->date;

        // Get categories affected by this transaction
        $categoryIds = [];
        if ($transaction->isSplit()) {
            $categoryIds = $transaction->splits->pluck('category_id')->toArray();
        } elseif ($transaction->category_id) {
            $categoryIds[] = $transaction->category_id;
        }

        foreach (array_unique($categoryIds) as $categoryId) {
            // Find active budget for this category
            $budget = \App\Models\Budget::where('user_id', $user->id)
                ->where('category_id', $categoryId)
                ->where('start_date', '<=', $date)
                ->where('end_date', '>=', $date)
                ->first();

            if ($budget) {
                // Calculate total expenses for this budget period
                $totalExpenses = Transaction::where('user_id', $user->id)
                    ->where('type', 'expense')
                    ->whereBetween('date', [$budget->start_date, $budget->end_date])
                    ->where(function ($query) use ($categoryId) {
                        $query->where('category_id', $categoryId)
                              ->orWhereHas('splits', function ($q) use ($categoryId) {
                                  $q->where('category_id', $categoryId);
                              });
                    })
                    ->with('splits') // Eager load splits to avoid N+1
                    ->get()
                    ->sum(function ($t) use ($categoryId) {
                        if ($t->isSplit()) {
                            return $t->splits->where('category_id', $categoryId)->sum('amount');
                        }
                        return $t->amount;
                    });

                $remaining = $budget->amount - $totalExpenses;
                $percentage = ($remaining / $budget->amount) * 100;

                if ($remaining < 0) {
                    $user->notify(new \App\Notifications\BudgetAlert($budget, "You have exceeded your budget for {$budget->category->name} by $" . abs($remaining)));
                } elseif ($percentage <= 20) {
                    $user->notify(new \App\Notifications\BudgetAlert($budget, "Your budget for {$budget->category->name} is running low. Only $" . $remaining . " remaining."));
                }
            }
        }
    }
}
