<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Course;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Instructor;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Students', Student::where('enrollment_status', 'active')->count())
                ->description('Active students')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),
            
            Stat::make('Total Instructors', Instructor::where('status', 'active')->count())
                ->description('Active instructors')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info'),
            
            Stat::make('Active Courses', Course::where('status', 'active')->count())
                ->description('Published courses')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('warning'),
            
            Stat::make('Total Enrollments', Enrollment::where('status', 'active')->count())
                ->description('Active enrollments')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary'),
        ];
    }
}
