<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;

class ForecastingService
{
    public function predictNextMonth(User $user)
    {
        // Get last 6 months of data
        $data = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->where('date', '>=', now()->subMonths(6)->startOfMonth())
            ->where('date', '<', now()->startOfMonth()) // Exclude current partial month
            ->selectRaw($this->getYearMonthSql() . ', sum(amount) as total')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        if ($data->count() < 3) {
            return null; // Not enough data to predict
        }

        $points = [];
        $i = 0;
        foreach ($data as $row) {
            $points[] = [$i, $row->total];
            $i++;
        }

        // Linear Regression: y = mx + b
        $n = count($points);
        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumXX = 0;

        foreach ($points as $point) {
            $x = $point[0];
            $y = $point[1];
            $sumX += $x;
            $sumY += $y;
            $sumXY += ($x * $y);
            $sumXX += ($x * $x);
        }

        $m = ($n * $sumXY - $sumX * $sumY) / ($n * $sumXX - $sumX * $sumX);
        $b = ($sumY - $m * $sumX) / $n;

        // Predict next month (index = $n)
        $predictedAmount = $m * $n + $b;

        return max(0, round($predictedAmount, 2));
    }
    private function getYearMonthSql()
    {
        $isSqlite = config('database.default') === 'sqlite' || config('database.connections.' . config('database.default') . '.driver') === 'sqlite';
        
        if ($isSqlite) {
            return 'strftime("%Y", date) as year, strftime("%m", date) as month';
        }
        
        return 'YEAR(date) as year, MONTH(date) as month';
    }
}
