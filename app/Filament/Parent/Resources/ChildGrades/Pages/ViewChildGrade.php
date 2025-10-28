<?php

namespace App\Filament\Parent\Resources\ChildGrades\Pages;

use App\Filament\Parent\Resources\ChildGrades\ChildGradeResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewChildGrade extends ViewRecord
{
    protected static string $resource = ChildGradeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
