<?php

namespace App\Listeners;

use App\Events\StartClass;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\StartClassNotification;

class SendClassStartedNotification implements ShouldQueue
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
    public function handle(StartClass $event): void
    {
        $classSession = $event->classSession;

        $students = $classSession->course->students()->where('enrollment_status', 'active')->with('user')->get();

        if($students) {
            foreach($students as $student) {
                $student->user->notify(new StartClassNotification(
                    $classSession, false, $student
                ));

                $parents = $student->parents()->with('user')->get();

                foreach($parents as $parent) {

                    if($parent->canViewChildGrades($student->id)) {
                         $parent->user->notify(new StartClassNotification($classSession, true, $student));
                    }
                }
            }
        }


    }
}
