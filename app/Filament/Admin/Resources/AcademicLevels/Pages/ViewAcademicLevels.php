<?php

namespace App\Filament\Admin\Resources\AcademicLevels\Pages;

use App\Filament\Admin\Resources\AcademicLevels\AcademicLevelsResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAcademicLevels extends ViewRecord
{
    protected static string $resource = AcademicLevelsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
