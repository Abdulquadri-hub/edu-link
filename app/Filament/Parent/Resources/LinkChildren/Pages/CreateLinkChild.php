<?php

namespace App\Filament\Parent\Resources\LinkChildren\Pages;

use App\Models\ChildLinkingRequest;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Parent\Resources\LinkChildren\LinkChildResource;

class CreateLinkChild extends CreateRecord
{
    protected static string $resource = LinkChildResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $parent = Auth::user()->parent;
        $data['parent_id'] = $parent->id;
        $data['status'] = 'pending';

        if (isset($data['is_new_student']) && $data['is_new_student']) {
            // Handle new student submission
            $data['student_id'] = null; // No existing student ID
            $data['new_student_data'] = [
                'first_name' => $data['new_student_first_name'],
                'last_name' => $data['new_student_last_name'],
                'email' => $data['new_student_email'] ?? null,
                'date_of_birth' => $data['new_student_dob'],
                'grade_level' => $data['new_student_grade_level'],
            ];
            
            // Remove temporary fields
            unset($data['new_student_first_name']);
            unset($data['new_student_last_name']);
            unset($data['new_student_email']);
            unset($data['new_student_dob']);
            unset($data['new_student_grade_level']);
            unset($data['is_new_student']);

            // The request will be created with student_id = null, and new_student_data populated.
            // Admin will see this as a request to register a new student and link them.

        } else {
            // Handle existing student linking
            if ($parent->children()->where('student_parent.student_id', $data['student_id'])->exists()) {
                Notification::make()
                    ->warning()
                    ->title('Already Linked')
                    ->body('This student is already linked to your account.')
                    ->send();
                
                $this->halt();
            }
            
            $existingRequest = ChildLinkingRequest::where('parent_id', $parent->id)
                ->where('student_id', $data['student_id'])
                ->where('status', 'pending')
                ->first();
            
            if ($existingRequest) {
                Notification::make()
                    ->warning()
                    ->title('Request Already Exists')
                    ->body('You already have a pending request for this student.')
                    ->send();
                
                $this->halt();
            }
        }
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Linking request submitted';
    }

    protected function getCreatedNotification(): ?Notification
    {
        $isNewStudent = $this->data['student_id'] === null;

        return Notification::make()
            ->success()
            ->title($isNewStudent ? 'New Student Registration & Linking Request Submitted' : 'Linking Request Submitted')
            ->body($isNewStudent ? 'Your child\'s details have been sent to administrators for registration and linking. You will be notified once it is processed.' : 'Your request has been sent to administrators for review. You will be notified once it is processed.')
            ->duration(5000);
    }
}
