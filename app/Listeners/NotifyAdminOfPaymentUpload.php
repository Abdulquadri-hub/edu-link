<?php

namespace App\Listeners;

use App\Models\User;
use App\Events\PaymentReceiptUploaded;
use App\Notifications\PaymentReceiptUploadedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyAdminOfPaymentUpload implements ShouldQueue
{
    public function handle(PaymentReceiptUploaded $event): void
    {
        $admins = User::where('user_type', 'admin')->get();
        
        foreach ($admins as $admin) {
            $admin->notify(
                new PaymentReceiptUploadedNotification($event->payment)
            );
        }
    }
}
