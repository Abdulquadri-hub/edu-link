<?php

namespace App\Listeners;

use App\Events\StudentPromoted;
use App\Notifications\StudentPromotedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyStudentOfPromotion implements ShouldQueue
{
    public function handle(StudentPromoted $event): void
    {
        // Notify the student
        $event->promotion->student->user->notify(
            new StudentPromotedNotification($event->promotion)
        );
        
        // Notify all parents
        $parents = $event->promotion->student->parents;
        foreach ($parents as $parent) {
            $parent->user->notify(
                new StudentPromotedNotification($event->promotion)
            );
        }
    }
}