<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendBillReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'budget:send-reminders';
    protected $description = 'Send bill reminders for upcoming recurring transactions';

    public function handle()
    {
        $today = now()->startOfDay();

        $transactions = \App\Models\RecurringTransaction::whereNotNull('reminder_days')
            ->whereNotNull('next_run_date')
            ->get();

        foreach ($transactions as $transaction) {
            $reminderDate = $transaction->next_run_date->copy()->subDays($transaction->reminder_days)->startOfDay();

            if ($today->equalTo($reminderDate)) {
                $transaction->user->notify(new \App\Notifications\BillReminder($transaction));
                $this->info("Sent reminder for: {$transaction->description}");
            }
        }
    }
}
