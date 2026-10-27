<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GoalAlertNotification extends Notification
{
    use Queueable;

    public $message;
    public $workout_id;

    public function __construct($message, $workout_id)
    {
        $this->message = $message;
        $this->workout_id = $workout_id;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->message,
            'workout_id' => $this->workout_id,
        ];
    }
}