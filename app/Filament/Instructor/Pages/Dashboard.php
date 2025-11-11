<?php

namespace App\Filament\Instructor\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Instructor\Widgets\InstructorStatsWidget;
use App\Filament\Instructor\Widgets\UpcomingClassesWidget;
use App\Filament\Instructor\Widgets\RecentAssignmentsWidget;

class Dashboard extends BaseDashboard
{
   protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';
    protected string $view = 'filament.instructor.pages.dashboard';

        public function getWidgets(): array
    {
        return [
            InstructorStatsWidget::class,
            UpcomingClassesWidget::class,
            RecentAssignmentsWidget::class,
        ];
    }

    public function getColumns(): int | array
    {
        return [
            'default' => 1,
            'sm' => 1,
            'md' => 2,
            'lg' => 4,
            'xl' => 4,
            '2xl' => 4,
        ];
    }

    public function getHeaderWidgets(): array
    {
        return [
            InstructorStatsWidget::class,
        ];
    }

    public function getFooterWidgets(): array
    {
        return [
            UpcomingClassesWidget::class,
            RecentAssignmentsWidget::class,
        ];
    }
}
