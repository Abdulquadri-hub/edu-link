<?php

namespace App\Listeners;

use App\Events\ParentUploadAssignment;
use App\Notifications\ParentUploadAssignmentNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendParentUploadAssignmentNotification implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ParentUploadAssignment $event): void
    {
        $parentAssignment = $event->parentAssignment;

        // Preferably notify all instructors attached to the assignment's course
        $instructors = $parentAssignment->assignment?->course?->instructors ?? [];

        foreach ($instructors as $instructor) {
            // Ensure the instructor has a user record with proper notification routing
            if (!empty($instructor->user)) {
                $instructor->user->notify(new ParentUploadAssignmentNotification($parentAssignment));
            }
        }
    }
}

