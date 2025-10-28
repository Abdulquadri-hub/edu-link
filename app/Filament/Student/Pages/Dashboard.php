<?php

namespace App\Filament\Student\Pages;

use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Student\Widgets\StudentStatsWidget;
use App\Filament\Student\Widgets\UpcomingClassesWidget;

class Dashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';
    
    public function getWidgets(): array
    {
        return [
            StudentStatsWidget::class,
            UpcomingClassesWidget::class,
        ];
    }
}
