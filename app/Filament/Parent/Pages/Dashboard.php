<?php

namespace App\Filament\Parent\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use App\Filament\Parent\Widgets\ParentStatsWidget;
use App\Filament\Parent\Widgets\RecentGradesWidget;
use App\Filament\Parent\Widgets\ChildrenOverviewWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Home;

    public function getWidgets(): array
    {
        return [
            ParentStatsWidget::class,
            ChildrenOverviewWidget::class,
            RecentGradesWidget::class,
        ];
    }
}
