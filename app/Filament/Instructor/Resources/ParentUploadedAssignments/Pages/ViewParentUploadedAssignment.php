<?php

namespace App\Filament\Instructor\Resources\ParentUploadedAssignments\Pages;

use App\Filament\Instructor\Resources\ParentUploadedAssignments\ParentUploadedAssignmentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewParentUploadedAssignment extends ViewRecord
{
    protected static string $resource = ParentUploadedAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
