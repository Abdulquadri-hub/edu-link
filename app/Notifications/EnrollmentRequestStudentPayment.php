<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Models\EnrollmentRequest;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Filament\Student\Resources\StudentPayments\StudentPaymentResource;


class EnrollmentRequestStudentPayment extends Notification implements ShouldQueue
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
        $course = $this->enrollmentRequest->course;
        $price = $this->enrollmentRequest->formattedPrice;
        $paymentsUrl = StudentPaymentResource::getUrl('create', panel: 'student');

        return (new MailMessage)
            ->subject('Action Required: Payment for Your Course Enrollment')
            ->greeting("Hello {$notifiable->student->user->first_name}!")
            ->line("You have a pending enrollment request for the course **{$course->title}**. To complete your enrollment, please submit the required payment.")
            ->line('')
            ->line('**Enrollment Details:**')
            ->line("Course: {$course->title}")
            ->line("Price: {$price}")
            ->line('')
            ->line('**Next Steps:**')
            ->line('1. Click the button below to go to the payment page.')
            ->line('2. Upload your payment receipt.')
            ->line('3. Wait for verification (1-2 business days).')
            ->line('4. You will be enrolled after verification.')
            ->action('Make Payment', $paymentsUrl)
            ->line('Thank you!');
    }

    public function toDatabase($notifiable): array
    {
        $course = $this->enrollmentRequest->course;
        $paymentsUrl = StudentPaymentResource::getUrl('create', panel: 'student');

        return \Filament\Notifications\Notification::make()
            ->title('Enrollment Payment Required')
            ->body("Payment is required to complete your enrollment in **{$course->title}**.")
            ->info()
            ->icon('heroicon-o-credit-card')
            ->actions([
                \Filament\Actions\Action::make('pay')
                    ->label('Make Payment')
                    ->url($paymentsUrl)
                    ->button(),
            ])
            ->getDatabaseMessage();
    }

    public function toArray($notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
