<?php

namespace App\Listeners;

use App\Events\EndClass;
use App\Notifications\EndClassNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendClassEndedNotification implements ShouldQueue
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
    public function handle(EndClass $event): void
    {
         $classSession = $event->classSession;

        $students = $classSession->course->students()->where('enrollment_status', 'active')->with('user')->get();

        if($students) {
            foreach($students as $student) {
                $student->user->notify(new EndClassNotification(
                    $classSession, false, $student
                ));

                $parents = $student->parents()->with('user')->get();

                foreach($parents as $parent) {
                    if($parent->canViewChildGrades($student->id)) {
                        $parent->user->notify(new EndClassNotification(
                            $classSession,
                            true,
                            $student
                        ));
                    }
                }
            }
        }
    }
}
