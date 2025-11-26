<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BillReminder extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $recurringTransaction;

    /**
     * Create a new notification instance.
     */
    public function __construct($recurringTransaction)
    {
        $this->recurringTransaction = $recurringTransaction;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $amount = $this->recurringTransaction->amount;
        $description = $this->recurringTransaction->description ?? 'Recurring Transaction';
        $dueDate = $this->recurringTransaction->next_run_date->format('M d, Y');

        return (new MailMessage)
            ->subject('Bill Reminder: ' . $description)
            ->line("This is a reminder that your bill for **{$description}** of **\${$amount}** is due on **{$dueDate}**.")
            ->action('View Dashboard', url('/dashboard'))
            ->line('Thank you for using Budget App!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => "Bill due soon: {$this->recurringTransaction->description} (\${$this->recurringTransaction->amount})",
            'amount' => $this->recurringTransaction->amount,
            'due_date' => $this->recurringTransaction->next_run_date,
            'recurring_transaction_id' => $this->recurringTransaction->id,
        ];
    }
}
