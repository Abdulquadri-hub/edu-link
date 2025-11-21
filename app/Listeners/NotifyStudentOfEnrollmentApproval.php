<?php

namespace App\Listeners;

use App\Events\EnrollmentApproved;
use App\Notifications\EnrollmentApprovedNotification;

class NotifyStudentOfEnrollmentApproval
{
    public function handle(EnrollmentApproved $event): void
    {
        // Notify the student
        $event->enrollmentRequest->student->user->notify(
            new EnrollmentApprovedNotification($event->enrollmentRequest)
        );
        
        // Also notify all parents
        $parents = $event->enrollmentRequest->student->parents;
        foreach ($parents as $parent) {
            $parent->user->notify(
                new EnrollmentApprovedNotification($event->enrollmentRequest)
            );
        }
    }
}
