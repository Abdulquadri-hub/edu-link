<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewPaymentUploadedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Payment $payment;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $student = $this->payment->student;
        $course = $this->payment->course;
        $uploader = $this->payment->uploaderName;
        $verifyUrl = route('filament.admin.resources.payment-verification.view', ['record' => $this->payment->id]);
        
        return (new MailMessage)
            ->subject('New Payment Receipt - Verification Required')
            ->greeting('Hello Administrator,')
            ->line('A new payment receipt has been uploaded and requires your verification.')
            ->line('')
            ->line('**Payment Details:**')
            ->line("Reference: {$this->payment->payment_reference}")
            ->line("Uploaded By: {$uploader}")
            ->line("Student: {$student->user->full_name} ({$student->student_id})")
            ->line("Course: {$course->title} ({$course->course_code})")
            ->line("Amount: {$this->payment->formattedAmount}")
            ->line("Payment Date: {$this->payment->payment_date->format('M d, Y')}")
            ->line("Payment Method: " . ucwords(str_replace('_', ' ', $this->payment->payment_method)))
            ->line('')
            ->line('**Additional Notes:**')
            ->line($this->payment->parent_notes ?: 'No additional notes provided')
            ->line('')
            ->action('Verify Payment', $verifyUrl)
            ->line('')
            ->line('Please review the payment receipt and verify or reject as appropriate.')
            ->salutation('EduLink System');
    }

    public function toDatabase($notifiable): array
    {
        $verifyUrl = route('filament.admin.resources.payment-verification.view', ['record' => $this->payment->id]);
        
        return \Filament\Notifications\Notification::make()
            ->title('New Payment Receipt Uploaded')
            ->body("{$this->payment->uploaderName} uploaded a payment receipt for {$this->payment->course->title} - {$this->payment->formattedAmount}")
            ->warning()
            ->icon('heroicon-o-currency-dollar')
            ->actions([
                \Filament\Actions\Action::make('verify')
                    ->label('Verify Payment')
                    ->url($verifyUrl)
                    ->button()
                    ->color('success'),
                \Filament\Actions\Action::make('view_student')
                    ->label('View Student')
                    ->url(route('filament.admin.resources.students.view', $this->payment->student_id))
                    ->button()
                    ->color('info'),
            ])
            ->getDatabaseMessage();
    }

    public function toArray($notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}