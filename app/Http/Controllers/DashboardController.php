<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $income = Transaction::where('user_id', $user->id)
            ->where('type', 'income')
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->sum('amount');

        $expenses = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->sum('amount');

        $budgets = Budget::where('user_id', $user->id)
            ->where('period', 'month')
            ->with('category')
            ->get();

        $totalBudget = $budgets->sum('amount');
        $remainingBudget = $totalBudget - $expenses;

        // Chart Data: Expenses by Category
        // Chart Data: Expenses by Category
        $transactions = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->with(['category', 'splits.category'])
            ->get();

        $categoryTotals = [];

        foreach ($transactions as $transaction) {
            if ($transaction->splits->isNotEmpty()) {
                foreach ($transaction->splits as $split) {
                    if ($split->category) {
                        $catName = $split->category->name;
                        if (!isset($categoryTotals[$catName])) {
                            $categoryTotals[$catName] = [
                                'category' => $catName,
                                'color' => $split->category->color,
                                'total' => 0,
                            ];
                        }
                        $categoryTotals[$catName]['total'] += $split->amount;
                    }
                }
            } else {
                if ($transaction->category) {
                    $catName = $transaction->category->name;
                    if (!isset($categoryTotals[$catName])) {
                        $categoryTotals[$catName] = [
                            'category' => $catName,
                            'color' => $transaction->category->color,
                            'total' => 0,
                        ];
                    }
                    $categoryTotals[$catName]['total'] += $transaction->amount;
                }
            }
        }

        $expensesByCategory = collect($categoryTotals)->values();

        // Advanced Analytics: Monthly Trend (Last 6 months)
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthName = $date->format('M Y');
            
            $monthlyIncome = Transaction::where('user_id', $user->id)
                ->where('type', 'income')
                ->whereMonth('date', $date->month)
                ->whereYear('date', $date->year)
                ->sum('amount');

            $monthlyExpense = Transaction::where('user_id', $user->id)
                ->where('type', 'expense')
                ->whereMonth('date', $date->month)
                ->whereYear('date', $date->year)
                ->sum('amount');

            $monthlyTrend[] = [
                'month' => $monthName,
                'income' => $monthlyIncome,
                'expense' => $monthlyExpense,
            ];
        }

        return Inertia::render('Dashboard', [
            'stats' => [
                'income' => $income,
                'expenses' => $expenses,
                'totalBudget' => $totalBudget,
                'remainingBudget' => $remainingBudget,
            ],
            'expensesByCategory' => $expensesByCategory,
            'monthlyTrend' => $monthlyTrend,
            'insights' => app(\App\Services\InsightService::class)->getInsights($user),
            'categories' => \App\Models\Category::where('user_id', $user->id)->get(),
            'recentTransactions' => Transaction::where('user_id', $user->id)
                ->when(request('search'), function ($query, $search) {
                    $query->where('description', 'like', "%{$search}%");
                })
                ->when(request('category'), function ($query, $category) {
                    $query->where('category_id', $category);
                })
                ->with(['category', 'splits'])
                ->latest('date')
                ->take(5)
                ->get(),
        ]);
    }
}
