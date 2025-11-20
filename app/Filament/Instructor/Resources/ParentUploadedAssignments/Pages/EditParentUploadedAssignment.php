<?php

namespace App\Filament\Instructor\Resources\ParentUploadedAssignments\Pages;

use App\Filament\Instructor\Resources\ParentUploadedAssignments\ParentUploadedAssignmentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditParentUploadedAssignment extends EditRecord
{
    protected static string $resource = ParentUploadedAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
