<?php

namespace App\Console\Commands;

use App\Models\Assignment;
use App\Notifications\AssignmentDueReminderNotification;
use Illuminate\Console\Command;

class SendAssignmentDueReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:assignment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders for assignments due in 24 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {

            $tomorrow = now()->addDay();
    
            $count = 0;
    
            $assignments = Assignment::where('status', 'published')
                ->whereDate('due_at', $tomorrow->toDateString())
                ->with('course.student.user')
                ->get();

            if(!$assignments) {
                return false;
            }
            
            foreach($assignments as $assignment) {
                $students = $assignment->course->students()
                   ->where('enrollment_status', 'active')
                   ->whereDoesntHave('submissions', function($query) use ($assignment) {
                      $query->where('assignment_id', $assignment->id);
                   })
                   ->with('user')
                   ->get();

                foreach($students as $student) {
                    $student->user->notify(new AssignmentDueReminderNotification($assignment));
                    $count++;
                }
            }

            $this->info("Sent {$count} assignment reminders");

        } catch (\Throwable $th) {
            $this->error("Error sending due reminder: {$th->getMessage()}");
        }
    }
}
