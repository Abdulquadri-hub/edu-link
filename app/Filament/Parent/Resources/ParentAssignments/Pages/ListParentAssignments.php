<?php

namespace App\Filament\Parent\Resources\ParentAssignments\Pages;

use App\Filament\Parent\Resources\ParentAssignments\ParentAssignmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListParentAssignments extends ListRecords
{
    protected static string $resource = ParentAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
