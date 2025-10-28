<?php

namespace App\Filament\Parent\Resources\ChildAttendances\Pages;

use App\Filament\Parent\Resources\ChildAttendances\ChildAttendanceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListChildAttendances extends ListRecords
{
    protected static string $resource = ChildAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
