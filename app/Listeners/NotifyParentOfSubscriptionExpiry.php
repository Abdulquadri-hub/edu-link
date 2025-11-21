<?php

namespace App\Listeners;

use App\Events\SubscriptionExpiring;
use App\Notifications\SubscriptionExpiringNotification;

class NotifyParentOfSubscriptionExpiry
{
    public function handle(SubscriptionExpiring $event): void
    {
        $parents = $event->subscription->student->parents;
        
        foreach ($parents as $parent) {
            $parent->user->notify(
                new SubscriptionExpiringNotification(
                    $event->subscription,
                    $event->daysRemaining
                )
            );
        }
    }
}
