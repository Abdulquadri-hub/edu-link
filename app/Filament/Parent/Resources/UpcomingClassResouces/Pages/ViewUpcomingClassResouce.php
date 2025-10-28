<?php

namespace App\Filament\Parent\Resources\UpcomingClassResouces\Pages;

use App\Filament\Parent\Resources\UpcomingClassResouces\UpcomingClassResouceResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewUpcomingClassResouce extends ViewRecord
{
    protected static string $resource = UpcomingClassResouceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // EditAction::make(),
        ];
    }
}
