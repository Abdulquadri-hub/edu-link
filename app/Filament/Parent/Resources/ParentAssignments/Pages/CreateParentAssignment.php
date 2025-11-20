<?php

namespace App\Filament\Parent\Resources\ParentAssignments\Pages;

use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Parent\Resources\ParentAssignments\ParentAssignmentResource;

class CreateParentAssignment extends CreateRecord
{
    protected static string $resource = ParentAssignmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['parent_id'] = Auth::user()->parent->id;
        $data['uploaded_at'] = now();
        if(empty($data['assignment_id'])) {
            $data['status'] = 'teach';
        }
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Assignment uploaded successfully';
    }
}
