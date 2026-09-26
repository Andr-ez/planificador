<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InactivityWarningNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $taskTitle,
        public readonly string $subjectName,
        public readonly int $daysRemaining,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line("La tarea '{$this->taskTitle}' de la materia '{$this->subjectName}' no ha tenido avances en 3 días. Quedan {$this->daysRemaining} días para la fecha límite.");
    }
}
