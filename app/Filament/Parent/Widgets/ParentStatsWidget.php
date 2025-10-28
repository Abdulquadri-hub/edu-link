<?php

namespace App\Filament\Parent\Widgets;

use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Contracts\Services\ParentServiceInterface;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class ParentStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $parent = Auth::user()->parent;

        $parentService = app(ParentServiceInterface::class);

        $dashboard = $parentService->getParentDashboard($parent->id);
        $childrenProgress = $dashboard['children_progress'];

        $totalChildren = count($childrenProgress);
        $avgProgress = $totalChildren > 0 
            ? round(collect($childrenProgress)->avg('progress'), 1) 
            : 0;
        $avgAttendance = $totalChildren > 0 
            ? round(collect($childrenProgress)->avg('attendance_rate'), 1) 
            : 0;
        
        $lowPerforming = collect($childrenProgress)->filter(function ($child) {
            return $child['progress'] < 60;
        })->count();

        return [
            Stat::make('My Children', $totalChildren)
                ->description('Enrolled students')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
            
            Stat::make('Average Progress', $avgProgress . '%')
                ->description('Overall performance')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info')
                ->chart([7, 3, 4, 5, 6, 3, 5]),
            
            Stat::make('Average Attendance', $avgAttendance . '%')
                ->description('Class attendance rate')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color($avgAttendance >= 85 ? 'success' : 'warning'),
            
            Stat::make('Needs Attention', $lowPerforming)
                ->description('Children below 60%')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowPerforming > 0 ? 'danger' : 'success'),
        ];
    }
}
