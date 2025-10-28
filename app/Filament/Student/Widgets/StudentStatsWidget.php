<?php

namespace App\Filament\Student\Widgets;

use App\Models\Student;
use App\Models\Assignment;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StudentStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $student = Auth::user()->student;
        $progress = $this->getStudentProgress($student->id);

        return [
            Stat::make('Enrolled Courses', $progress['enrollments'])
                ->description('Active courses')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('success')
                ->chart([7, 3, 4, 5, 6, 3, 5]),
            
            Stat::make('Overall Progress', round($progress['overall_progress'], 1) . '%')
                ->description('Average across all courses')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),
            
            Stat::make('Attendance Rate', round($progress['attendance_rate'], 1) . '%')
                ->description('Class attendance')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color(fn () => $progress['attendance_rate'] >= 85 ? 'success' : 'warning'),
            
            Stat::make('Pending Assignments', $this->getPendingCount())
                ->description('Not submitted yet')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),
        ];
    }

    private function getPendingCount(): int
    {
        $student = Auth::user()->student;
        
        return Assignment::whereHas('course.enrollments', function ($query) use ($student) {
            $query->where('student_id', $student->id)->where('status', 'active');
        })
        ->where('status', 'published')
        ->where('due_at', '>', now())
        ->whereDoesntHave('submissions', function ($q) use ($student) {
            $q->where('student_id', $student->id);
        })
        ->count();
    }

    private function getStudentProgress()
    {
        $student = Auth::user()->student;

        return [
            'overall_progress' => $student->calculateOverallProgress(),
            'attendance_rate' => $student->calculateAttendanceRate(),
            'enrollments' =>  $this->getEnrrolments(),
        ];
    }

    private function getEnrrolments() {
        $student = Auth::user()->student;

        return $student->activeEnrollments()->count();
        
    }
}
