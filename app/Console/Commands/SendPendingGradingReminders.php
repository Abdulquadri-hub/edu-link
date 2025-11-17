<?php

namespace App\Console\Commands;

use App\Models\Instructor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\PendingGradingReminderMail;

class SendPendingGradingReminders extends Command
{
    protected $signature = 'reminders:pending-grading';
    protected $description = 'Remind instructors about pending submissions';

    public function handle()
    {
        try {

            $instructors = Instructor::where('status', 'active')
                ->with('user')
                ->get();
    
            $count = 0;
            if(!$instructors) {
                return false;
            }


            foreach ($instructors as $instructor) {
                $pendingCount = \App\Models\Submission::whereHas('assignment', function ($query) use ($instructor) {
                    $query->where('instructor_id', $instructor->id);
                })
                ->where('status', 'submitted')
                ->doesntHave('grade')
                ->count();
    
                if ($pendingCount > 0) {
                    Mail::to($instructor->user->email)->send(
                        new PendingGradingReminderMail($instructor, $pendingCount)
                    );
                    $count++;
                }
            }
    
            $this->info("Sent reminders to {$count} instructors");
        } catch (\Throwable $th) {
           $this->error("Error sending pending grading reminders: {$th->getMessage()}");
        }
    }
}

