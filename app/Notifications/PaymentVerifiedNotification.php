<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Actions\Action as FilamentAction;

class PaymentVerifiedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Payment $payment
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray($notifiable): array
    {
        $body = "Your payment of {$this->payment->formattedAmount} has been verified";
        
        if ($this->payment->hasSubscription()) {
            $body .= ". Subscription {$this->payment->subscription->subscription_code} is now active!";
        }

        return FilamentNotification::make()
            ->title('Payment Verified ✓')
            ->body($body)
            ->success()
            ->icon('heroicon-o-check-badge')
            ->actions([
                FilamentAction::make('view')
                    ->label('View Payment')
                    ->url(route('filament.parent.resources.payments.view', $this->payment->id))
                    ->button(),
            ])
            ->getDatabaseMessage();
    }

    public function toMail($notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Payment Verified - ' . $this->payment->payment_reference)
            ->line('Great news! Your payment has been verified.')
            ->line("Amount: {$this->payment->formattedAmount}")
            ->line("Reference: {$this->payment->payment_reference}")
            ->line("Student: {$this->payment->student->user->full_name}")
            ->line("Course: {$this->payment->course->title}");

        if ($this->payment->hasSubscription()) {
            $message->line("Subscription Code: {$this->payment->subscription->subscription_code}")
                    ->action('View Subscription', route('filament.parent.resources.payments.view', $this->payment->id));
        }

        return $message;
    }
}