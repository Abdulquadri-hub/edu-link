<?php

namespace App\Listeners;

use App\Events\GradePublished;
use App\Notifications\GradePublishedNotification;
use App\Notifications\LowGradeAlertNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendGradeNotification implements ShouldQueue
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
    public function handle(GradePublished $event): void
    {
        $grade = $event->grade;
        $student = $grade->submission->student;

        // notify student
        $student->user->notify(new GradePublishedNotification($grade));

        // notify parents if grade is < 60%
        if($grade->percentage < 60) {
            $parents = $student->parents()->with('user')->get();

            foreach($parents as $parent) {
               if($parent->canViewChildGrades($student->id)) {
                    $parent->user->notify(new LowGradeAlertNotification($grade, $student));
               }

            }
        }
    }
}
