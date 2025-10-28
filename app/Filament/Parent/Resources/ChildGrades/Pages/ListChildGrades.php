<?php

namespace App\Filament\Parent\Resources\ChildGrades\Pages;

use App\Filament\Parent\Resources\ChildGrades\ChildGradeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListChildGrades extends ListRecords
{
    protected static string $resource = ChildGradeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
