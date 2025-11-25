<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Models\ExpenseRequest;

class ExpenseRequestStatusUpdated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public ExpenseRequest $expenseRequest, public string $status, public string $approverName)
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
            'status' => $this->status,
            'approver_name' => $this->approverName,
            'message' => 'Your request for $' . number_format($this->expenseRequest->amount, 2) . ' was ' . $this->status,
            'type' => 'status_updated',
        ];
    }
}
