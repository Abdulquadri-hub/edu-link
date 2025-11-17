<?php

namespace App\Listeners;

use App\Events\ClassScheduled;
use App\Notifications\ClassScheduledNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendClassNotification implements ShouldQueue
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
    public function handle(ClassScheduled $event): void
    {
        $classSession = $event->classSession;

        // get all student  for the class course and notify
        $students = $classSession->course->students()->where('enrollment_status', 'active')->with('user')->get();

        //notify student
        foreach($students as $student) {
            $student->user->notify(new ClassScheduledNotification($classSession));
        }

        //notify instuctor
        $classSession->instructor->user->notify(new ClassScheduledNotification($classSession));
    }
}
