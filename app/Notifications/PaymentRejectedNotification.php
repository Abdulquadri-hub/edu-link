<?php

namespace App\Notifications;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Actions\Action as FilamentAction;

class PaymentRejectedNotification extends Notification
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
        return FilamentNotification::make()
            ->title('Payment Rejected')
            ->body("Your payment of {$this->payment->formattedAmount} was rejected. Reason: {$this->payment->admin_notes}")
            ->danger()
            ->icon('heroicon-o-x-circle')
            ->actions([
                FilamentAction::make('reupload')
                    ->label('Upload New Receipt')
                    ->url(route('filament.parent.resources.payments.create'))
                    ->button()
                    ->color('danger'),
            ])
            ->getDatabaseMessage();
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment Rejected - ' . $this->payment->payment_reference)
            ->line('Your payment has been rejected.')
            ->line("Reference: {$this->payment->payment_reference}")
            ->line("Amount: {$this->payment->formattedAmount}")
            ->line("Reason: {$this->payment->admin_notes}")
            ->line('Please upload a valid payment receipt.');
    }
}