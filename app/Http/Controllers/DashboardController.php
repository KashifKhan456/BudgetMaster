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
        $expensesByCategory = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->with('category')
            ->selectRaw('category_id, sum(amount) as total')
            ->groupBy('category_id')
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->category->name,
                    'color' => $item->category->color,
                    'total' => $item->total,
                ];
            });

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
            'recentTransactions' => Transaction::where('user_id', $user->id)
                ->with('category')
                ->latest('date')
                ->take(5)
                ->get(),
        ]);
    }
}
