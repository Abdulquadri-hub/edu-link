<?php

namespace App\Filament\Parent\Resources\ChildAttendances\Pages;

use App\Filament\Parent\Resources\ChildAttendances\ChildAttendanceResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditChildAttendance extends EditRecord
{
    protected static string $resource = ChildAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
