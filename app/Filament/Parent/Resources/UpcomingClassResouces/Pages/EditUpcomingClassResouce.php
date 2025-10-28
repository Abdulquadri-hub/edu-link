<?php

namespace App\Filament\Parent\Resources\UpcomingClassResouces\Pages;

use App\Filament\Parent\Resources\UpcomingClassResouces\UpcomingClassResouceResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUpcomingClassResouce extends EditRecord
{
    protected static string $resource = UpcomingClassResouceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
