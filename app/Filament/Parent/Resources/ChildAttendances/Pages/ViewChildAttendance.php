<?php

namespace App\Filament\Parent\Resources\ChildAttendances\Pages;

use App\Filament\Parent\Resources\ChildAttendances\ChildAttendanceResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewChildAttendance extends ViewRecord
{
    protected static string $resource = ChildAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // EditAction::make(),
        ];
    }
}
