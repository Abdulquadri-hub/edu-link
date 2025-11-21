<?php

namespace App\Listeners;

use App\Events\PaymentVerified;
use App\Notifications\PaymentVerifiedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyParentOfPaymentVerification implements ShouldQueue
{
    public function handle(PaymentVerified $event): void
    {
        // Notify the parent
        $event->payment->parent->user->notify(
            new PaymentVerifiedNotification($event->payment)
        );
        
        // Also notify the student
        $event->payment->student->user->notify(
            new PaymentVerifiedNotification($event->payment)
        );
    }
}
