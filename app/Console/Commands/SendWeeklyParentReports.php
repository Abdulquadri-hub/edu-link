<?php

namespace App\Console\Commands;

use App\Models\ParentModel;
use App\Mail\WeeklySummaryMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendWeeklyParentReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:weekly-parents';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send weekly progress reports to parents';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {

            $parents = ParentModel::where('recieves_weekly_report', true)
                ->with(['children.user', 'children.enrollments', 'children.grades', 'user'])
                ->get();
            $count = 0;
    
            if(!$parents) {
                return false;
            }
            
            foreach($parents as $parent) {
                $summary = [];
    
                foreach($parent->children as $child) {
                    $summary[] = [
                        'name' => $child->user->full_name,
                        'courses' => $child->activeEnrollments()->count(),
                        'average_grade' => $child->grades()->where('is_published', true)->avg('percentage'),
                        'attendance_rate' => $child->calculateAttendanceRate(),
                    ];
                }
    
                Mail::to($parent->user->email)->send(new WeeklySummaryMail($parent, $summary));
                $count++;
            }
          
            $this->info("Sent {$count} weekly reports to parents");  
        } catch (\Throwable $th) {
            $this->error("Error sending weekly reports to parent: {$th->getMessage()}");
        }
    }
}
