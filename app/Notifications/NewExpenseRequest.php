<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Models\ExpenseRequest;

class NewExpenseRequest extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public ExpenseRequest $expenseRequest)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'request_id' => $this->expenseRequest->id,
            'amount' => $this->expenseRequest->amount,
            'requester_name' => $this->expenseRequest->user->name,
            'message' => $this->expenseRequest->user->name . ' requested $' . number_format($this->expenseRequest->amount, 2),
            'type' => 'new_request',
        ];
    }
}
