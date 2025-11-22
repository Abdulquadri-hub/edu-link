<?php

namespace App\Filament\Student\Resources\MyEnrollmentRequests\Pages;

use App\Filament\Student\Resources\MyEnrollmentRequests\MyEnrollmentRequestResource;
use App\Models\User;
use App\Models\ParentModel;
use App\Services\StudentService;
use App\Services\ParentService;
use App\Notifications\NewParentEnrollmentNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateMyEnrollmentRequest extends CreateRecord
{
    protected static string $resource = MyEnrollmentRequestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $student = Auth::user()->student;
        $data['student_id'] = $student->id;
        $data['status'] = 'pending'; // Initial status

        $studentService = app(StudentService::class);
        $isMinor = $studentService->isMinor($student->id);
        $hasParent = $student->parents()->exists();

        if ($isMinor) {
            if ($hasParent) {
                // Scenario 1: Minor with Parent - Existing flow
                // The existing flow should handle the notification to the parent.
                // We will rely on the EnrollmentRequestCreated event to handle this in a later phase.
                // Set status to indicate parent has been notified and payment is pending
                $data['status'] = 'parent_notified';
            } else {
                // Scenario 2: Minor without Parent - Prompt for parent info and create parent
                if (!isset($data['new_parent_first_name'])) {
                    // This should be handled by the form's conditional fields, but as a safeguard:
                    Notification::make()
                        ->danger()
                        ->title('Parent Information Required')
                        ->body('As a minor, you must provide parent/guardian information to proceed with enrollment.')
                        ->send();
                    $this->halt();
                }

                // Create Parent Account
                $parentService = app(ParentService::class);
                $tempPassword = Str::random(10);

                $parentData = [
                    'first_name' => $data['new_parent_first_name'],
                    'last_name' => $data['new_parent_last_name'],
                    'email' => $data['new_parent_email'],
                    'username' => $data['new_parent_email'],
                    'password' => $tempPassword,
                    'phone' => $data['new_parent_phone'],
                    'address' => $data['new_parent_address'],
                    'city' => $data['new_parent_city'],
                    'state' => $data['new_parent_state'],
                    'country' => $data['new_parent_country'],
                    'occupation' => $data['new_parent_occupation'] ?? 'N/A',
                ];

                $parent = $parentService->createParent($parentData);
                
                // Link Parent to Student
                $parentService->linkChild($parent->id, $student->id, [
                    'relationship' => $data['new_parent_relationship'],
                    'is_primary_contact' => true,
                    'can_view_grades' => true,
                    'can_view_attendance' => true,
                ]);

                // Notify the new parent
                $parent->user->notify(new NewParentEnrollmentNotification(
                    $parent->user,
                    $student->user,
                    $this->getRecord(), // EnrollmentRequest is not created yet, so we need to pass the data
                    $tempPassword
                ));

                // Set status to indicate parent has been notified and payment is pending
                $data['status'] = 'parent_notified';
            }
        } else {
            // Scenario 3: Adult Student - Notify student directly and set status to payment pending
            // We will create a new notification for the adult student in a later phase.
            // For now, set status to 'payment_pending'.
            $data['status'] = 'payment_pending';
        }

        // Clean up temporary form fields
        unset($data['new_parent_first_name']);
        unset($data['new_parent_last_name']);
        unset($data['new_parent_email']);
        unset($data['new_parent_phone']);
        unset($data['new_parent_address']);
        unset($data['new_parent_city']);
        unset($data['new_parent_state']);
        unset($data['new_parent_country']);
        unset($data['new_parent_occupation']);
        unset($data['new_parent_relationship']);

        return $data;
    }

    protected function afterCreate(): void
    {
        // Handle the existing flow notification for Scenario 1 (Minor with Parent)
        $student = Auth::user()->student;
        $studentService = app(StudentService::class);
        $isMinor = $studentService->isMinor($student->id);
        $hasParent = $student->parents()->exists();

        if ($isMinor && $hasParent) {
            // Existing flow: Notify parent
            $this->record->notifyParent(); // This should dispatch the EnrollmentRequestCreated event
        }
        
        // Handle Adult Student notification in a later phase (Scenario 3)
        if (!$isMinor) {
            // TODO: Dispatch a new notification to the student with payment details
            // For now, the status is set to 'payment_pending' in mutateFormDataBeforeCreate
        }
    }
}
{
    protected static string $resource = MyEnrollmentRequestResource::class;
}
