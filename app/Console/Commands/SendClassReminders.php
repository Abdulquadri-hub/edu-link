<?php

namespace App\Console\Commands;

use App\Models\ClassSession;
use App\Notifications\ClassReminderNotification;
use Illuminate\Console\Command;


class SendClassReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:class';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders for classes starting in 24 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $tomorrow = now()->addDay();
    
            $classes = ClassSession::where('status', 'scheduled')
                        ->where('scheduled_at', $tomorrow->toDateString())->with(['course.students.user', 'instructor.user'])
                        ->get();
           
            $count = 0;
    
            if(!$classes) {
                return false;
            }
            foreach($classes as $class) {
    
                $students = $class->course->students()
                    ->where('enrollment_status', 'active')
                    ->with('user')
                    ->get();
    
                foreach($students as $student) {
                    $student->user->notify(new ClassReminderNotification($class));
                    $count++;
                }
    
                $class->instructor->user->notify(new ClassReminderNotification($class));
                $count++;
            }
    
            $this->info("Sent {$count} class reminders");
            
        } catch (\Throwable $th) {
            $this->error("Error sending reminders: {$th->getMessage()}");
        }
    }
}
