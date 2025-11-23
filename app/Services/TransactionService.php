<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class TransactionService
{
    public function getTransactions(int $userId, array $filters)
    {
        $query = Transaction::where('user_id', $userId)->with('category');

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

        return $query->latest('date')->get();
    }

    public function createTransaction(array $data): Transaction
    {
        return Transaction::create(array_merge($data, ['user_id' => Auth::id()]));
    }

    public function updateTransaction(Transaction $transaction, array $data): bool
    {
        return $transaction->update($data);
    }

    public function deleteTransaction(Transaction $transaction): bool
    {
        return $transaction->delete();
    }
}
