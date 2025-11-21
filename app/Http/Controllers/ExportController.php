<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class ExportController extends Controller
{
    public function export()
    {
        $transactions = Transaction::where('user_id', Auth::id())
            ->with('category')
            ->orderBy('date', 'desc')
            ->get();

        $csvFileName = 'transactions_export_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use($transactions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Type', 'Category', 'Amount', 'Description']);

            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->date,
                    $transaction->type,
                    $transaction->category ? $transaction->category->name : 'Uncategorized',
                    $transaction->amount,
                    $transaction->description
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
