<?php

namespace App\Listeners;

use App\Events\ParentUploadAssignment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendParentUploadAssignmentNotification
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

        /**
         * when parent upload assignment and status === teach notify instructor that the assignment is given to the child from school and need his attention to do it or teach in class this can be know from the notes or not.
         * When parent upload and the status === submitted notify instuctor to grade the assignment.
         * get the uploaded assignment by parent
         * get instructor from parent->student->instructor()->user->notify()
         */

        // $instructor = $parentAssignment->
    }
}

