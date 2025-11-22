<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PaymentVerifiedNotification extends Notification implements ShouldQueue
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
        $subscription = $this->payment->subscription;
        $isStudent = $this->payment->isStudentPayment();
        
        $greeting = $isStudent 
            ? "Hello {$student->user->first_name}!"
            : "Hello {$notifiable->user->first_name}!";
        
        $studentReference = $isStudent 
            ? "Your"
            : "Your child {$student->user->full_name}'s";
        
        $mail = (new MailMessage)
            ->subject('Payment Verified - Enrollment Confirmed')
            ->greeting($greeting)
            ->line("Great news! {$studentReference} payment has been verified and approved.")
            ->line('')
            ->line('**Payment Details:**')
            ->line("Reference: {$this->payment->payment_reference}")
            ->line("Amount: {$this->payment->formattedAmount}")
            ->line("Course: {$course->title}")
            ->line("Payment Date: {$this->payment->payment_date->format('M d, Y')}")
            ->line("Verified On: {$this->payment->verified_at->format('M d, Y H:i')}")
            ->line('');
        
        if ($subscription) {
            $mail->line('**Subscription Created:**')
                ->line("Subscription Code: {$subscription->subscription_code}")
                ->line("Frequency: {$subscription->frequencyText}")
                ->line("Start Date: {$subscription->start_date->format('M d, Y')}")
                ->line("End Date: {$subscription->end_date->format('M d, Y')}")
                ->line("Total Sessions: {$subscription->total_sessions}")
                ->line("Status: Active ✓")
                ->line('');
        }
        
        $mail->line('**What Happens Next:**')
            ->line($isStudent 
                ? '• You can now access the course materials'
                : '• Your child can now access the course materials')
            ->line('• Class schedules will be available in the dashboard')
            ->line('• You will receive notifications for upcoming classes')
            ->line('• Progress tracking is now active')
            ->line('')
            ->action($isStudent ? 'View My Courses' : 'View Child\'s Progress', 
                    $isStudent 
                        ? route('filament.student.resources.courses.index')
                        : route('filament.parent.pages.child-progress', ['child' => $student->id]))
            ->line('')
            ->line('Thank you for your payment. We look forward to a successful learning journey!')
            ->salutation('Best regards,')
            ->salutation('The EduLink Team');
        
        if ($this->payment->admin_notes) {
            $mail->line('')
                ->line('**Admin Notes:**')
                ->line($this->payment->admin_notes);
        }
        
        return $mail;
    }

    public function toDatabase($notifiable): array
    {
        $isStudent = $this->payment->isStudentPayment();
        $viewUrl = $isStudent 
            ? route('filament.student.resources.student-payments.view', $this->payment->id)
            : route('filament.parent.resources.payments.view', $this->payment->id);
        
        $coursesUrl = $isStudent
            ? route('filament.student.resources.courses.index')
            : route('filament.parent.pages.dashboard');
        
        return \Filament\Notifications\Notification::make()
            ->title('Payment Verified!')
            ->body("Payment for {$this->payment->course->title} has been verified. {$this->payment->formattedAmount} - Enrollment confirmed!")
            ->success()
            ->icon('heroicon-o-check-circle')
            ->actions([
                \Filament\Actions\Action::make('view_payment')
                    ->label('View Payment')
                    ->url($viewUrl)
                    ->button(),
                \Filament\Actions\Action::make('view_courses')
                    ->label($isStudent ? 'My Courses' : 'View Progress')
                    ->url($coursesUrl)
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