<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BudgetAlert extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $budget;
    public $message;

    /**
     * Create a new notification instance.
     */
    public function __construct($budget, $message)
    {
        $this->budget = $budget;
        $this->message = $message;
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
            'message' => $this->message,
            'budget_id' => $this->budget->id,
            'category_name' => $this->budget->category->name,
            'amount' => $this->budget->amount,
        ];
    }
}
