<?php

namespace App\Notifications;

use App\Models\EnrollmentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class EnrollmentRequestCreatedNotification extends Notification
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
        $frequency = $this->enrollmentRequest->frequencyText;
        $price = $this->enrollmentRequest->formattedPrice;
        $paymentsUrl = route('filament.parent.resources.payments.create');
        
        return (new MailMessage)
            ->subject('Course Enrollment Request - Payment Required')
            ->greeting("Hello {$notifiable->user->first_name}!")
            ->line("Your child, **{$student->user->full_name}**, has requested to enroll in a new course.")
            ->line('')
            ->line('**Enrollment Request Details:**')
            ->line("Request Code: {$this->enrollmentRequest->request_code}")
            ->line("Course: {$course->title} ({$course->course_code})")
            ->line("Frequency: {$frequency}")
            ->line("Price: {$price}")
            ->line("Duration: {$course->duration_weeks} weeks")
            ->line('')
            ->line('**Student\'s Message:**')
            ->line($this->enrollmentRequest->student_message ?: 'No message provided')
            ->line('')
            ->line('**Next Steps - Payment Required:**')
            ->line('1. Log in to your Parent Portal')
            ->line('2. Navigate to "Payments" section')
            ->line('3. Upload payment receipt')
            ->line('4. Wait for verification (1-2 business days)')
            ->line('5. Your child will be enrolled after verification')
            ->line('')
            ->action('Upload Payment Receipt', $paymentsUrl)
            ->line('')
            ->line('**Course Information:**')
            ->line("Title: {$course->title}")
            ->line("Level: " . ucfirst($course->level))
            ->line("Category: " . ucfirst($course->category))
            ->line("Credit Hours: {$course->credit_hours}")
            ->line('')
            ->line('If you have any questions, please contact our administration team.')
            ->salutation('Best regards,')
            ->salutation('The EduLink Team');
    }

    public function toDatabase($notifiable): array
    {
        return \Filament\Notifications\Notification::make()
            ->title('New Enrollment Request')
            ->body("{$this->enrollmentRequest->student->user->full_name} has requested enrollment in {$this->enrollmentRequest->course->title}")
            ->info()
            ->icon('heroicon-o-academic-cap')
            ->actions([
                \Filament\Actions\Action::make('view_request')
                    ->label('View Request')
                    ->url(route('filament.parent.pages.dashboard'))
                    ->button(),
                \Filament\Actions\Action::make('upload_payment')
                    ->label('Upload Payment')
                    ->url(route('filament.parent.resources.payments.create'))
                    ->button()
                    ->color('success'),
            ])
            ->getDatabaseMessage();
    }

    public function toArray($notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}