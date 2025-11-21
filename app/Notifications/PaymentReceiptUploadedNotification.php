<?php

namespace App\Notifications;


use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Actions\Action as FilamentAction;

class PaymentReceiptUploadedNotification extends Notification
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
            ->title('New Payment Receipt')
            ->body("{$this->payment->parent->user->full_name} uploaded a payment receipt for {$this->payment->formattedAmount}")
            ->warning()
            ->icon('heroicon-o-currency-dollar')
            ->actions([
                FilamentAction::make('verify')
                    ->label('Verify Payment')
                    ->url(route('filament.admin.resources.payment-verification.view', $this->payment->id))
                    ->button()
                    ->color('warning'),
            ])
            ->getDatabaseMessage();
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Payment Receipt Uploaded')
            ->line('A new payment receipt has been uploaded.')
            ->line("Parent: {$this->payment->parent->user->full_name}")
            ->line("Amount: {$this->payment->formattedAmount}")
            ->line("Student: {$this->payment->student->user->full_name}")
            ->line("Course: {$this->payment->course->title}")
            ->action('Verify Payment', route('filament.admin.resources.payment-verification.view', $this->payment->id));
    }
}
