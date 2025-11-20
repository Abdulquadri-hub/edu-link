<?php

namespace App\Filament\Admin\Resources\AcademicLevels\Pages;

use App\Filament\Admin\Resources\AcademicLevels\AcademicLevelsResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAcademicLevels extends EditRecord
{
    protected static string $resource = AcademicLevelsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
