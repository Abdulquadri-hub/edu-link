<?php

namespace App\Notifications;

use App\Models\EnrollmentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Actions\Action as FilamentAction;

class EnrollmentRequestCreatedNotification extends Notification
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
            ->title('New Enrollment Request')
            ->body("{$this->enrollmentRequest->student->user->full_name} requested enrollment in {$this->enrollmentRequest->course->title} - {$this->enrollmentRequest->formattedPrice}")
            ->info()
            ->icon('heroicon-o-academic-cap')
            ->actions([
                FilamentAction::make('upload_payment')
                    ->label('Upload Payment')
                    ->url(route('filament.parent.resources.payments.create'))
                    ->button()
                    ->color('primary'),
            ])
            ->getDatabaseMessage();
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Course Enrollment Request - ' . $this->enrollmentRequest->request_code)
            ->line("Your child has requested to enroll in a course.")
            ->line("Student: {$this->enrollmentRequest->student->user->full_name}")
            ->line("Course: {$this->enrollmentRequest->course->title}")
            ->line("Frequency: {$this->enrollmentRequest->frequencyText}")
            ->line("Price: {$this->enrollmentRequest->formattedPrice}")
            ->line("Request Code: {$this->enrollmentRequest->request_code}")
            ->line('Please upload payment to proceed with enrollment.');
    }
}