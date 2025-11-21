<?php

namespace App\Notifications;


use App\Models\StudentPromotion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Filament\Notifications\Notification as FilamentNotification;

class PromotionRejectedNotification extends Notification
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
            ->title('Promotion Request Rejected')
            ->body("The promotion request for {$this->promotion->student->user->full_name} was rejected. Reason: {$this->promotion->rejection_reason}")
            ->danger()
            ->icon('heroicon-o-x-circle')
            ->getDatabaseMessage();
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Promotion Request Rejected')
            ->line('A promotion request has been rejected.')
            ->line("Student: {$this->promotion->student->user->full_name}")
            ->line("Reason: {$this->promotion->rejection_reason}");
    }
}
