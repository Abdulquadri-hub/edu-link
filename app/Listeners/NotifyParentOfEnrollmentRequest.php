<?php

namespace App\Listeners;

use App\Events\EnrollmentRequestCreated;
use App\Notifications\EnrollmentRequestCreatedNotification;

class NotifyParentOfEnrollmentRequest
{
    public function handle(EnrollmentRequestCreated $event): void
    {
        // Notify all parents of the student
        $parents = $event->enrollmentRequest->student->parents;
        
        foreach ($parents as $parent) {
            $parent->user->notify(
                new EnrollmentRequestCreatedNotification($event->enrollmentRequest)
            );
        }
    }
}
