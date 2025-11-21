<?php

namespace App\Listeners;

use App\Events\ParentAssignmentSubmitted;
use App\Notifications\ParentAssignmentSubmittedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyInstructorOfParentAssignment implements ShouldQueue
{
    public function handle(ParentAssignmentSubmitted $event): void
    {
        // Notify all instructors of the course
        $instructors = $event->parentAssignment->assignment->course->instructors;
        
        foreach ($instructors as $instructor) {
            $instructor->user->notify(
                new ParentAssignmentSubmittedNotification($event->parentAssignment)
            );
        }
    }
}