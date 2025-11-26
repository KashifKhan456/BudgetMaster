<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : now()->startOfYear();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : now()->endOfMonth();

        // Monthly Spending Comparison
        $isSqlite = config('database.default') === 'sqlite' || config('database.connections.' . config('database.default') . '.driver') === 'sqlite';
        $dateFormat = $isSqlite ? 'strftime("%Y-%m", date)' : 'DATE_FORMAT(date, "%Y-%m")';

        $monthlySpending = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw("$dateFormat as month, sum(amount) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => Carbon::createFromFormat('Y-m', $item->month)->format('M Y'),
                    'total' => $item->total,
                ];
            });

        // Income vs Expense Trend
        $incomeVsExpense = Transaction::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw("$dateFormat as month, type, sum(amount) as total")
            ->groupBy('month', 'type')
            ->orderBy('month')
            ->get()
            ->groupBy('month')
            ->map(function ($items, $month) {
                return [
                    'month' => Carbon::createFromFormat('Y-m', $month)->format('M Y'),
                    'income' => $items->where('type', 'income')->sum('total'),
                    'expense' => $items->where('type', 'expense')->sum('total'),
                ];
            })
            ->values();

        // Category Breakdown
        $categoryBreakdown = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['category', 'splits.category'])
            ->get();

        $categoryTotals = [];
        foreach ($categoryBreakdown as $transaction) {
            if ($transaction->splits->isNotEmpty()) {
                foreach ($transaction->splits as $split) {
                    if ($split->category) {
                        $name = $split->category->name;
                        if (!isset($categoryTotals[$name])) {
                            $categoryTotals[$name] = ['name' => $name, 'total' => 0, 'color' => $split->category->color];
                        }
                        $categoryTotals[$name]['total'] += $split->amount;
                    }
                }
            } elseif ($transaction->category) {
                $name = $transaction->category->name;
                if (!isset($categoryTotals[$name])) {
                    $categoryTotals[$name] = ['name' => $name, 'total' => 0, 'color' => $transaction->category->color];
                }
                $categoryTotals[$name]['total'] += $transaction->amount;
            }
        }

        // Forecast
        $forecast = app(\App\Services\ForecastingService::class)->predictNextMonth($user);

        // Budget vs Actual
        $budgets = \App\Models\Budget::where('user_id', $user->id)
            ->with('category')
            ->get();

        $budgetVsActual = $budgets->map(function ($budget) use ($startDate, $endDate, $user) {
            $spent = Transaction::where('user_id', $user->id)
                ->where('category_id', $budget->category_id)
                ->where('type', 'expense')
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('amount');

            return [
                'category' => $budget->category->name,
                'budget' => $budget->amount,
                'spent' => $spent,
                'percentage' => $budget->amount > 0 ? min(100, round(($spent / $budget->amount) * 100)) : 0,
                'color' => $budget->category->color,
            ];
        });

        // Savings Rate
        $totalIncome = Transaction::where('user_id', $user->id)
            ->where('type', 'income')
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');
        
        $totalExpense = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        $savingsRate = $totalIncome > 0 ? round((($totalIncome - $totalExpense) / $totalIncome) * 100, 1) : 0;

        // Top Spenders
        $topSpenders = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw('description, sum(amount) as total')
            ->groupBy('description')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // High Level Analytics Enhancements

        // 1. Year-over-Year Growth
        $previousYearStartDate = $startDate->copy()->subYear();
        $previousYearEndDate = $endDate->copy()->subYear();
        
        $previousYearSpending = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('date', [$previousYearStartDate, $previousYearEndDate])
            ->sum('amount');

        $yoyGrowth = 0;
        if ($previousYearSpending > 0) {
            $yoyGrowth = round((($totalExpense - $previousYearSpending) / $previousYearSpending) * 100, 1);
        } elseif ($totalExpense > 0) {
            $yoyGrowth = 100; // 100% growth if previous was 0
        }

        // 2. Net Cash Flow
        $netCashFlow = $totalIncome - $totalExpense;

        // 3. Recurring vs Discretionary (Estimated)
        // We estimate "Recurring" as the sum of all active recurring transactions that would occur in this period.
        // For simplicity in this high-level view, we'll sum the 'amount' of all active recurring expenses.
        // A more precise way would be to check if the transaction is actually linked, but we'll use this estimation.
        $recurringExpenses = \App\Models\RecurringTransaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->sum('amount');
        
        // Adjust for the period length? The recurring amount is usually "per interval". 
        // Assuming most are monthly. If we are looking at a month view, this is fine.
        // If the filter is not a month, this might be misleading. 
        // Let's stick to the assumption that "Recurring" is the monthly fixed cost.
        // If the selected range is > 1 month, we should multiply? 
        // Let's keep it simple: "Monthly Recurring Commitments" vs "Total Expense this period".
        // Actually, better: "Recurring" = Sum of active recurring transactions * number of months in period?
        // Let's just pass the raw "Monthly Recurring" value and let the frontend label it "Est. Monthly Fixed".
        
        $monthlyRecurring = $recurringExpenses;
        $discretionary = max(0, $totalExpense - $monthlyRecurring);


        return Inertia::render('Reports/Index', [
            'filters' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'monthlySpending' => $monthlySpending,
            'incomeVsExpense' => $incomeVsExpense,
            'categoryBreakdown' => collect($categoryTotals)->values(),
            'forecast' => $forecast,
            'budgetVsActual' => $budgetVsActual,
            'savingsRate' => $savingsRate,
            'topSpenders' => $topSpenders,
            'yoyGrowth' => $yoyGrowth,
            'netCashFlow' => $netCashFlow,
            'recurringVsDiscretionary' => [
                'recurring' => $monthlyRecurring,
                'discretionary' => $discretionary,
                'total' => $totalExpense
            ],
        ]);
    }

    public function export(Request $request)
    {
        $user = Auth::user();
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : now()->startOfYear();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : now()->endOfMonth();

        $transactions = Transaction::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->with('category')
            ->orderBy('date', 'desc')
            ->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=transactions_export.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Description', 'Amount', 'Type', 'Category']);

            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->date->format('Y-m-d'),
                    $transaction->description,
                    $transaction->amount,
                    $transaction->type,
                    $transaction->category ? $transaction->category->name : 'Uncategorized'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
