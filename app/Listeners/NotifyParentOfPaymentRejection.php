<?php

namespace App\Listeners;


use App\Events\PaymentRejected;
use Illuminate\Notifications\Messages\MailMessage;

class NotifyParentOfPaymentRejection
{
    public function handle(PaymentRejected $event): void
    {
        $parent = $event->payment->parent->user;
        
        $parent->notify(new class($event->payment) extends \Illuminate\Notifications\Notification {
            use \Illuminate\Bus\Queueable;
            
            public function __construct(public $payment) {}
            
            public function via($notifiable): array
            {
                return ['database', 'mail'];
            }
            
            public function toDatabase($notifiable): array
            {
                return [
                    'title' => 'Payment Rejected',
                    'message' => "Your payment of {$this->payment->formattedAmount} was rejected",
                    'reference' => $this->payment->payment_reference,
                    'reason' => $this->payment->admin_notes,
                ];
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
        });
    }
}