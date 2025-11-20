<?php

namespace App\Filament\Parent\Resources\ParentAssignments\Pages;

use App\Filament\Parent\Resources\ParentAssignments\ParentAssignmentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewParentAssignment extends ViewRecord
{
    protected static string $resource = ParentAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn () => $this->getRecord()->status === 'pending'),
        ];
    }
}
