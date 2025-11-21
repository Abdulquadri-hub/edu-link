<?php

namespace App\Notifications;


use App\Models\StudentPromotion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Filament\Notifications\Notification as FilamentNotification;

class StudentPromotedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public StudentPromotion $promotion
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray($notifiable): array
    {
        return FilamentNotification::make()
            ->title('Grade Promotion!')
            ->body("Congratulations! You have been promoted to {$this->promotion->toLevel->name}")
            ->success()
            ->icon('heroicon-o-arrow-trending-up')
            ->getDatabaseMessage();
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Grade Promotion - ' . $this->promotion->toLevel->name)
            ->line('Congratulations on your promotion!')
            ->line("You have been promoted to: {$this->promotion->toLevel->name}")
            ->line("Promotion Type: {$this->promotion->promotionTypeText}")
            ->line("Effective Date: {$this->promotion->effective_date->format('M d, Y')}")
            ->line('Keep up the great work!');
    }
}