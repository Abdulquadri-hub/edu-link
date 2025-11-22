<?php

namespace App\Notifications;

use App\Models\EnrollmentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Actions\Action as FilamentAction;

class EnrollmentApprovedNotification extends Notification
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
            ->title('Enrollment Approved!')
            ->body("You have been enrolled in {$this->enrollmentRequest->course->title}. Welcome to the course!")
            ->success()
            ->icon('heroicon-o-check-badge')
            ->actions([
                FilamentAction::make('view_course')
                    ->label('View Course')
                    ->url(route('filament.student.resources.courses.view', $this->enrollmentRequest->course_id))
                    ->button()
                    ->color('success'),
            ])
            ->getDatabaseMessage();
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Enrollment Approved - Welcome to ' . $this->enrollmentRequest->course->title)
            ->line('Congratulations! Your enrollment request has been approved.')
            ->line("Course: {$this->enrollmentRequest->course->title}")
            ->line("Request Code: {$this->enrollmentRequest->request_code}")
            ->line('You can now access course materials and attend classes.')
            ->action('View Course', route('filament.student.resources.courses.view', $this->enrollmentRequest->course_id));
    }
}

