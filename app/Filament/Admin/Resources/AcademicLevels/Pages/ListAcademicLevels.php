<?php

namespace App\Filament\Admin\Resources\AcademicLevels\Pages;

use App\Filament\Admin\Resources\AcademicLevels\AcademicLevelsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAcademicLevels extends ListRecords
{
    protected static string $resource = AcademicLevelsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
