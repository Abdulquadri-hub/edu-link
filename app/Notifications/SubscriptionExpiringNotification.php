<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Actions\Action as FilamentAction;

class SubscriptionExpiringNotification extends Notification
{
    use Queueable;

    protected Subscription $subscription;
    protected int $daysRemaining;

    public function __construct(Subscription $subscription, int $daysRemaining)
    {
        $this->subscription = $subscription;
        $this->daysRemaining = $daysRemaining;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $student = $this->subscription->student;
        $course = $this->subscription->course;
        $isStudent = $notifiable->id === $student->user_id;
        
        $greeting = $isStudent 
            ? "Hello {$student->user->first_name}!"
            : "Hello {$notifiable->first_name}!";
        
        $message = $isStudent
            ? "Your subscription for {$course->title} is expiring soon."
            : "Your child {$student->user->full_name}'s subscription for {$course->title} is expiring soon.";
        
        return (new MailMessage)
            ->subject('Subscription Expiring Soon - Action Required')
            ->greeting($greeting)
            ->line($message)
            ->line('')
            ->line('**Subscription Details:**')
            ->line("Subscription Code: {$this->subscription->subscription_code}")
            ->line("Course: {$course->title}")
            ->line("Frequency: {$this->subscription->frequencyText}")
            ->line("End Date: {$this->subscription->end_date->format('M d, Y')}")
            ->line("Days Remaining: **{$this->daysRemaining} days**")
            ->line("Sessions Remaining: {$this->subscription->sessions_remaining}")
            ->line('')
            ->line('**To Continue Learning:**')
            ->line('1. Make a payment for subscription renewal')
            ->line('2. Upload payment receipt')
            ->line('3. Wait for verification')
            ->line('4. Your subscription will be extended')
            ->line('')
            ->action($isStudent ? 'Renew Subscription' : 'Upload Payment', 
                    $isStudent 
                        ? route('filament.student.resources.student-payments.create')
                        : route('filament.parent.resources.payments.create'))
            ->line('')
            ->line('Don\'t miss out on your learning journey. Renew today!')
            ->salutation('Best regards,')
            ->salutation('The EduLink Team');
    }

    public function toDatabase($notifiable): array
    {
        $student = $this->subscription->student;
        $course = $this->subscription->course;
        $isStudent = $notifiable->id === $student->user_id;
        
        $renewUrl = $isStudent
            ? route('filament.student.resources.student-payments.create')
            : route('filament.parent.resources.payments.create');
        
        return FilamentNotification::make()
            ->title('Subscription Expiring Soon ⏰')
            ->body("{$course->title} subscription expires in {$this->daysRemaining} days. {$this->subscription->sessions_remaining} sessions remaining.")
            ->warning()
            ->icon('heroicon-o-exclamation-triangle')
            ->actions([
                FilamentAction::make('renew')
                    ->label('Renew Now')
                    ->url($renewUrl)
                    ->button()
                    ->color('warning'),
                FilamentAction::make('view_subscription')
                    ->label('View Details')
                    ->url($isStudent 
                        ? route('filament.student.resources.courses.view', $course->id)
                        : route('filament.parent.pages.dashboard'))
                    ->button(),
            ])
            ->getDatabaseMessage();
    }

    public function toArray($notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}