<?php

namespace App\Services;

use App\Models\RecurringTransaction;
use Illuminate\Support\Facades\Auth;

class RecurringTransactionService
{
    public function getRecurringTransactions(int $userId, array $filters = [])
    {
        $query = RecurringTransaction::where('user_id', $userId)->with('category');

        if (!empty($filters['search'])) {
            $query->where('description', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->paginate(10)->withQueryString();
    }

    public function createRecurringTransaction(array $data): RecurringTransaction
    {
        $data['user_id'] = Auth::id();
        $data['next_run_date'] = $data['start_date']; // Initial run date
        return RecurringTransaction::create($data);
    }

    public function updateRecurringTransaction(RecurringTransaction $transaction, array $data): bool
    {
        return $transaction->update($data);
    }

    public function deleteRecurringTransaction(RecurringTransaction $transaction): bool
    {
        return $transaction->delete();
    }
}
