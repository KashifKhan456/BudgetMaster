<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProcessRecurringTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'budget:process-recurring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process recurring transactions that are due';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();

        $recurringTransactions = RecurringTransaction::whereDate('next_run_date', '<=', $today)->get();

        $count = 0;

        foreach ($recurringTransactions as $recurring) {
            DB::transaction(function () use ($recurring, &$count) {
                // Create the transaction
                Transaction::create([
                    'user_id' => $recurring->user_id,
                    'category_id' => $recurring->category_id,
                    'amount' => $recurring->amount,
                    'type' => $recurring->type,
                    'date' => $recurring->next_run_date, // Use the scheduled date, not necessarily today
                    'description' => $recurring->description . ' (Recurring)',
                ]);

                // Update next run date
                $nextDate = Carbon::parse($recurring->next_run_date);
                
                switch ($recurring->interval) {
                    case 'daily':
                        $nextDate->addDay();
                        break;
                    case 'weekly':
                        $nextDate->addWeek();
                        break;
                    case 'monthly':
                        $nextDate->addMonth();
                        break;
                    case 'yearly':
                        $nextDate->addYear();
                        break;
                }

                $recurring->update(['next_run_date' => $nextDate]);
                $count++;
            });
        }

        $this->info("Processed {$count} recurring transactions.");
    }
}
