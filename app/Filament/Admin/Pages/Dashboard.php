<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
     public function getWidgets(): array
    {
        return [
            \App\Filament\Admin\Widgets\StatsOverviewWidget::class,
        ];
    }
}