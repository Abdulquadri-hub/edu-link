<?php

namespace App\Listeners;

use App\Events\AssignmentCreated;
use App\Notifications\AssignmentCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendAssignmentNotification implements ShouldQueue
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
    public function handle(AssignmentCreated $event): void
    {
        $assignment = $event->assignment;

        if($assignment->status === 'published') {
            $students = $assignment->course->students()->where('enrollment_status', 'active')->with('user')->get();

            //notify students
            foreach($students as $student) {
                $student->user->notify(new AssignmentCreatedNotification($assignment));
            }
        }
    }
}
