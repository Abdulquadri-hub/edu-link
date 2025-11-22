<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PaymentRejectedNotification extends Notification implements ShouldQueue
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
        $isStudent = $this->payment->isStudentPayment();
        $paymentsUrl = $isStudent 
            ? route('filament.student.resources.student-payments.create')
            : route('filament.parent.resources.payments.create');
        
        $greeting = $isStudent 
            ? "Hello {$student->user->first_name},"
            : "Hello {$notifiable->user->first_name},";
        
        $studentReference = $isStudent 
            ? "your"
            : "your child {$student->user->full_name}'s";
        
        return (new MailMessage)
            ->subject('Payment Verification Issue - Action Required')
            ->greeting($greeting)
            ->line("Unfortunately, {$studentReference} payment could not be verified.")
            ->line('')
            ->line('**Payment Details:**')
            ->line("Reference: {$this->payment->payment_reference}")
            ->line("Amount: {$this->payment->formattedAmount}")
            ->line("Course: {$course->title}")
            ->line("Submitted: {$this->payment->created_at->format('M d, Y')}")
            ->line('')
            ->line('**Reason for Rejection:**')
            ->line($this->payment->admin_notes ?: 'Please contact administration for details')
            ->line('')
            ->line('**What You Need to Do:**')
            ->line('1. Review the rejection reason above')
            ->line('2. Correct the issue (e.g., clearer receipt, correct amount)')
            ->line('3. Upload a new payment receipt')
            ->line('4. Or contact our administration team for assistance')
            ->line('')
            ->action('Upload New Receipt', $paymentsUrl)
            ->line('')
            ->line('**Need Help?**')
            ->line('If you believe this is an error or need clarification, please contact our administration team.')
            ->line('Email: admin@edulink.com')
            ->line('Phone: +1 (555) 123-4567')
            ->line('')
            ->line('We\'re here to help resolve this issue quickly.')
            ->salutation('Best regards,')
            ->salutation('The EduLink Team');
    }

    public function toDatabase($notifiable): array
    {
        $isStudent = $this->payment->isStudentPayment();
        $uploadUrl = $isStudent 
            ? route('filament.student.resources.student-payments.create')
            : route('filament.parent.resources.payments.create');
        
        $viewUrl = $isStudent 
            ? route('filament.student.resources.student-payments.view', $this->payment->id)
            : route('filament.parent.resources.payments.view', $this->payment->id);
        
        return \Filament\Notifications\Notification::make()
            ->title('Payment Rejected')
            ->body("Payment for {$this->payment->course->title} was rejected. Reason: " . ($this->payment->admin_notes ?: 'Please contact administration'))
            ->danger()
            ->icon('heroicon-o-x-circle')
            ->actions([
                \Filament\Actions\Action::make('view_details')
                    ->label('View Details')
                    ->url($viewUrl)
                    ->button(),
                \Filament\Actions\Action::make('reupload')
                    ->label('Upload New Receipt')
                    ->url($uploadUrl)
                    ->button()
                    ->color('warning'),
            ])
            ->getDatabaseMessage();
    }

    public function toArray($notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}