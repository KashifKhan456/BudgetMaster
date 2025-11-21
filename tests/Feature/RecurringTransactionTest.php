<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use Carbon\Carbon;

class RecurringTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_recurring_transaction_is_processed()
    {
        $this->travelTo(Carbon::parse('2025-11-20 12:00:00'));
        $user = User::factory()->create();
        
        $recurring = RecurringTransaction::create([
            'user_id' => $user->id,
            'amount' => 100,
            'type' => 'expense',
            'interval' => 'monthly',
            'start_date' => Carbon::today(),
            'next_run_date' => Carbon::today(),
            'description' => 'Test Recurring',
        ]);

        Artisan::call('budget:process-recurring');

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'amount' => 100,
            'description' => 'Test Recurring (Recurring)',
        ]);

        $this->assertDatabaseHas('recurring_transactions', [
            'id' => $recurring->id,
            'next_run_date' => '2025-12-20 00:00:00',
        ]);
    }

    public function test_future_recurring_transaction_is_not_processed()
    {
        $this->travelTo(Carbon::parse('2025-11-20 12:00:00'));
        $user = User::factory()->create();
        
        $recurring = RecurringTransaction::create([
            'user_id' => $user->id,
            'amount' => 100,
            'type' => 'expense',
            'interval' => 'monthly',
            'start_date' => Carbon::tomorrow(),
            'next_run_date' => Carbon::tomorrow(),
            'description' => 'Future Recurring',
        ]);

        Artisan::call('budget:process-recurring');

        $this->assertDatabaseMissing('transactions', [
            'description' => 'Future Recurring (Recurring)',
        ]);

        $this->assertDatabaseHas('recurring_transactions', [
            'id' => $recurring->id,
            'next_run_date' => '2025-11-21 00:00:00',
        ]);
    }
}
