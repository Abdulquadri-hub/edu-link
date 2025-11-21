<?php

namespace App\Notifications;

use App\Models\EnrollmentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Filament\Notifications\Notification as FilamentNotification;

class EnrollmentRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public EnrollmentRequest $enrollmentRequest
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray($notifiable): array
    {
        return FilamentNotification::make()
            ->title('Enrollment Request Rejected')
            ->body("Your enrollment request for {$this->enrollmentRequest->course->title} was rejected. Reason: {$this->enrollmentRequest->rejection_reason}")
            ->danger()
            ->icon('heroicon-o-x-circle')
            ->getDatabaseMessage();
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Enrollment Request Rejected')
            ->line('Your enrollment request has been rejected.')
            ->line("Course: {$this->enrollmentRequest->course->title}")
            ->line("Reason: {$this->enrollmentRequest->rejection_reason}");
    }
}
