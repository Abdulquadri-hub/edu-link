<?php

namespace App\Filament\Instructor\Widgets;

use App\Models\Submission;
use App\Models\ClassSession;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InstructorStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $instructor = Auth::user()->instructor;
        
        // Calculate stats
        $activeCourses = $instructor->courses()->where('status', 'active')->count();
        $totalStudents = $instructor->getStudentCount();
        $pendingGrading = Submission::whereHas('assignment', function ($query) use ($instructor) {
            $query->where('instructor_id', $instructor->id);
        })
        ->where('status', 'submitted')
        ->doesntHave('grade')
        ->count();
        
        $monthlyHours = $instructor->calculateMonthlyHours(now()->month, now()->year);
        $lastMonthHours = $instructor->calculateMonthlyHours(now()->subMonth()->month, now()->subMonth()->year);
        $hoursDifference = $monthlyHours - $lastMonthHours;
        
        // Get chart data for last 7 days
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $hours = ClassSession::where('instructor_id', $instructor->id)
                ->whereDate('started_at', $date)
                ->where('status', 'completed')
                ->sum('duration_minutes');
            $chartData[] = round($hours / 60, 1);
        }

        return [
            Stat::make('My Active Courses', $activeCourses)
                ->description('Currently teaching')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('success')
                ->chart($activeCourses > 0 ? [3, 5, 4, 6, 7, 8, 6] : []),
            
            Stat::make('Total Students', $totalStudents)
                ->description('Enrolled across all courses')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info')
                ->chart($totalStudents > 0 ? [10, 15, 20, 25, 28, 30, $totalStudents] : []),
            
            Stat::make('Pending Grading', $pendingGrading)
                ->description($pendingGrading > 0 ? 'Submissions awaiting grades' : 'All caught up!')
                ->descriptionIcon($pendingGrading > 0 ? 'heroicon-m-exclamation-circle' : 'heroicon-m-check-circle')
                ->color($pendingGrading > 0 ? 'warning' : 'success')
                ->url(route('filament.instructor.resources.submissions.index')),
            
            Stat::make('This Month', number_format($monthlyHours, 1) . ' hrs')
                ->description($hoursDifference >= 0 
                    ? '+' . number_format($hoursDifference, 1) . ' hrs from last month' 
                    : number_format($hoursDifference, 1) . ' hrs from last month')
                ->descriptionIcon($hoursDifference >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($hoursDifference >= 0 ? 'success' : 'danger')
                ->chart($chartData),
        ];
    }
}
