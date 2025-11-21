<?php

namespace App\Listeners;

use App\Events\ChildLinkingApproved;
use App\Notifications\ChildLinkingApprovedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyParentOfLinkingApproval implements ShouldQueue
{
    public function handle(ChildLinkingApproved $event): void
    {
        // Notify the parent
        $event->linkingRequest->parent->user->notify(
            new ChildLinkingApprovedNotification($event->linkingRequest)
        );
    }
}

