<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Filament\Notifications\Notification as FilamentNotification;

class SubscriptionExpiredNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Subscription $subscription
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray($notifiable): array
    {
        return FilamentNotification::make()
            ->title('Subscription Expired')
            ->body("The subscription for {$this->subscription->course->title} has expired")
            ->danger()
            ->icon('heroicon-o-exclamation-triangle')
            ->getDatabaseMessage();
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Subscription Expired - ' . $this->subscription->course->title)
            ->line('Your subscription has expired.')
            ->line("Student: {$this->subscription->student->user->full_name}")
            ->line("Course: {$this->subscription->course->title}")
            ->line("Expired: {$this->subscription->end_date->format('M d, Y')}")
            ->line('Please renew to continue accessing the course.');
    }
}