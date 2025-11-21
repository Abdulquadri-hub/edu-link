<?php

namespace App\Notifications;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Filament\Notifications\Notification as FilamentNotification;

class SubscriptionExpiringNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Subscription $subscription,
        public int $daysRemaining
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray($notifiable): array
    {
        return FilamentNotification::make()
            ->title('Subscription Expiring Soon')
            ->body("Subscription for {$this->subscription->course->title} expires in {$this->daysRemaining} days. {$this->subscription->sessions_remaining} sessions remaining.")
            ->warning()
            ->icon('heroicon-o-clock')
            ->getDatabaseMessage();
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Subscription Expiring Soon - ' . $this->subscription->course->title)
            ->line("Your subscription is expiring in {$this->daysRemaining} days.")
            ->line("Student: {$this->subscription->student->user->full_name}")
            ->line("Course: {$this->subscription->course->title}")
            ->line("Expires: {$this->subscription->end_date->format('M d, Y')}")
            ->line("Sessions Remaining: {$this->subscription->sessions_remaining}")
            ->line('Please renew to continue accessing the course.');
    }
}
