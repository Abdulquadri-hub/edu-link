<?php

namespace App\Filament\Parent\Resources\ChildGrades\Pages;

use App\Filament\Parent\Resources\ChildGrades\ChildGradeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditChildGrade extends EditRecord
{
    protected static string $resource = ChildGradeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
