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
        
        // Determine status: 'teach' if uploading for course (no assignment), otherwise 'pending' for assignment submission
        if (!empty($data['assignment_id'])) {
            $data['status'] = 'pending';
        } elseif (!empty($data['course_id'])) {
            $data['status'] = 'teach';
        } else {
            $data['status'] = 'pending';
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
