<?php

namespace App\Filament\Admin\Pages;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
     public function getWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
        ];
    }
}