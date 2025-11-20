<?php

namespace App\Filament\Instructor\Resources\ParentUploadedAssignments\Pages;

use App\Filament\Instructor\Resources\ParentUploadedAssignments\ParentUploadedAssignmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListParentUploadedAssignments extends ListRecords
{
    protected static string $resource = ParentUploadedAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
