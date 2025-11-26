<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class InsightService
{
    public function getInsights(User $user): array
    {
        $insights = [];

        $trends = $this->analyzeTrends($user);
        if ($trends) {
            $insights = array_merge($insights, $trends);
        }

        $forecast = $this->forecastSpending($user);
        if ($forecast) {
            $insights[] = $forecast;
        }

        $anomalies = $this->detectAnomalies($user);
        if ($anomalies) {
            $insights = array_merge($insights, $anomalies);
        }

        return $insights;
    }

    protected function analyzeTrends(User $user): array
    {
        $currentMonth = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();

        $currentMonthExpenses = $this->getMonthlyExpensesByCategory($user, $currentMonth);
        $lastMonthExpenses = $this->getMonthlyExpensesByCategory($user, $lastMonth);

        $trends = [];

        foreach ($currentMonthExpenses as $categoryId => $amount) {
            if (isset($lastMonthExpenses[$categoryId]) && $lastMonthExpenses[$categoryId] > 0) {
                $lastAmount = $lastMonthExpenses[$categoryId];
                $diff = $amount - $lastAmount;
                $percentage = ($diff / $lastAmount) * 100;

                if ($percentage > 20) {
                    $categoryName = $this->getCategoryName($categoryId);
                    $trends[] = [
                        'type' => 'trend',
                        'level' => 'warning',
                        'message' => "You're spending " . round($percentage) . "% more on {$categoryName} compared to last month.",
                        'icon' => 'pi pi-chart-line',
                        'color' => 'text-red-500',
                    ];
                } elseif ($percentage < -20) {
                    $categoryName = $this->getCategoryName($categoryId);
                    $trends[] = [
                        'type' => 'trend',
                        'level' => 'success',
                        'message' => "You're spending " . round(abs($percentage)) . "% less on {$categoryName} compared to last month.",
                        'icon' => 'pi pi-chart-line',
                        'color' => 'text-green-500',
                    ];
                }
            }
        }

        return $trends;
    }

    protected function forecastSpending(User $user): ?array
    {
        $today = Carbon::now();
        $daysPassed = $today->day;
        $daysInMonth = $today->daysInMonth;

        if ($daysPassed < 5) {
            return null; // Too early to forecast
        }

        $currentMonthExpenses = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereMonth('date', $today->month)
            ->whereYear('date', $today->year)
            ->sum('amount');

        $dailyAverage = $currentMonthExpenses / $daysPassed;
        $projectedTotal = $dailyAverage * $daysInMonth;

        // Compare with total budget
        $totalBudget = \App\Models\Budget::where('user_id', $user->id)
            ->where('period', 'month')
            ->sum('amount');

        if ($totalBudget > 0) {
            $diff = $totalBudget - $projectedTotal;
            if ($diff > 0) {
                return [
                    'type' => 'forecast',
                    'level' => 'success',
                    'message' => "On track to save $" . round($diff) . " this month based on current spending.",
                    'icon' => 'pi pi-wallet',
                    'color' => 'text-green-500',
                ];
            } else {
                return [
                    'type' => 'forecast',
                    'level' => 'danger',
                    'message' => "Projected to overspend by $" . round(abs($diff)) . " if you keep this pace.",
                    'icon' => 'pi pi-exclamation-circle',
                    'color' => 'text-red-500',
                ];
            }
        }

        return null;
    }

    protected function detectAnomalies(User $user): array
    {
        $anomalies = [];
        $currentMonth = Carbon::now();

        // Get average spending for last 3 months per category
        $averages = [];
        for ($i = 1; $i <= 3; $i++) {
            $date = Carbon::now()->subMonths($i);
            $expenses = $this->getMonthlyExpensesByCategory($user, $date);
            foreach ($expenses as $catId => $amount) {
                if (!isset($averages[$catId])) {
                    $averages[$catId] = [];
                }
                $averages[$catId][] = $amount;
            }
        }

        $currentExpenses = $this->getMonthlyExpensesByCategory($user, $currentMonth);

        foreach ($currentExpenses as $catId => $amount) {
            if (isset($averages[$catId]) && count($averages[$catId]) >= 2) {
                $avg = array_sum($averages[$catId]) / count($averages[$catId]);
                if ($avg > 50 && $amount > $avg * 1.5) { // Only flag if avg > $50 and spending is 50% higher
                    $categoryName = $this->getCategoryName($catId);
                    $percentage = (($amount - $avg) / $avg) * 100;
                    $anomalies[] = [
                        'type' => 'anomaly',
                        'level' => 'warning',
                        'message' => "Unusual spike in {$categoryName}: " . round($percentage) . "% higher than your 3-month average.",
                        'icon' => 'pi pi-bolt',
                        'color' => 'text-yellow-500',
                    ];
                }
            }
        }

        return $anomalies;
    }

    protected function getMonthlyExpensesByCategory(User $user, Carbon $date): array
    {
        $transactions = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereMonth('date', $date->month)
            ->whereYear('date', $date->year)
            ->with(['splits'])
            ->get();

        $totals = [];
        foreach ($transactions as $transaction) {
            if ($transaction->isSplit()) {
                foreach ($transaction->splits as $split) {
                    if (!isset($totals[$split->category_id])) $totals[$split->category_id] = 0;
                    $totals[$split->category_id] += $split->amount;
                }
            } elseif ($transaction->category_id) {
                if (!isset($totals[$transaction->category_id])) $totals[$transaction->category_id] = 0;
                $totals[$transaction->category_id] += $transaction->amount;
            }
        }
        return $totals;
    }

    protected function getCategoryName($id)
    {
        return \App\Models\Category::find($id)?->name ?? 'Unknown';
    }
}
