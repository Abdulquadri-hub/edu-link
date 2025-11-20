<?php

namespace App\Filament\Parent\Resources\ParentAssignments\Pages;

use App\Filament\Parent\Resources\ParentAssignments\ParentAssignmentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditParentAssignment extends EditRecord
{
    protected static string $resource = ParentAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Assignment updated successfully';
    }

    // Prevent editing if already submitted
    protected function authorizeAccess(): void
    {
        parent::authorizeAccess();

        $record = $this->getRecord();
        
        if ($record->status !== 'pending') {
            abort(403, 'Cannot edit submitted assignments');
        }
    }
}
