<?php

namespace App\Listeners;

use App\Models\User;
use App\Events\ChildLinkingRequestCreated;
use App\Notifications\ChildLinkingRequestNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyAdminOfChildLinkingRequest implements ShouldQueue
{
    public function handle(ChildLinkingRequestCreated $event): void
    {
        // Notify all admins
        $admins = User::where('user_type', 'admin')->get();
        
        foreach ($admins as $admin) {
            $admin->notify(
                new ChildLinkingRequestNotification($event->linkingRequest)
            );
        }
    }
}