<?php

namespace App\Notifications;

use App\Models\EnrollmentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;

class EnrollmentRequestAdminReview extends Notification implements ShouldQueue
{
    use Queueable;

    protected EnrollmentRequest $enrollmentRequest;

    public function __construct(EnrollmentRequest $enrollmentRequest)
    {
        $this->enrollmentRequest = $enrollmentRequest;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $student = $this->enrollmentRequest->student;
        $course = $this->enrollmentRequest->course;
        $adminDashboardUrl = route('filament.admin.resources.enrollment-requests.index');

        return (new MailMessage)
            ->subject('Enrollment Request Ready for Review')
            ->greeting("Hello Admin!")
            ->line("A payment has been submitted for an enrollment request and is ready for your review.")
            ->line("Student: **{$student->user->full_name}**")
            ->line("Course: **{$course->title}**")
            ->action('Review Enrollment Request', $adminDashboardUrl)
            ->line('Please review the payment and approve or reject the enrollment request.');
    }

    public function toDatabase($notifiable): array
    {
        $student = $this->enrollmentRequest->student;
        $course = $this->enrollmentRequest->course;
        $adminDashboardUrl = route('filament.admin.resources.enrollment-requests.index');

        return \Filament\Notifications\Notification::make()
            ->title('Enrollment Request for Review')
            ->body("Payment submitted for **{$student->user->full_name}**'s enrollment in **{$course->title}**.")
            ->info()
            ->icon('heroicon-o-document-check')
            ->actions([
                \Filament\Actions\Action::make('review')
                    ->label('Review')
                    ->url($adminDashboardUrl)
                    ->button(),
            ])
            ->getDatabaseMessage();
    }

    public function toArray($notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
