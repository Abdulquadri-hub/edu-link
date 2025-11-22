<?php

namespace App\Filament\Parent\Resources\LinkChildren\Pages;

use App\Models\ChildLinkingRequest;
use App\Services\StudentService;
use App\Notifications\NewStudentWelcomeNotification;
use Illuminate\Support\Str;
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
            try {
                $studentService = app(StudentService::class);
                
                $tempPassword = Str::random(10); 

                $studentData = [
                    'first_name' => $data['new_student_first_name'],
                    'last_name' => $data['new_student_last_name'],
                    'email' => $data['new_student_email'] ?? null,
                    'username' => $data['new_student_email'] ? str_replace('@gmai.com', '', $data['new_student_email']) : Str::lower(Str::random(10)),
	                'password' => $tempPassword, 
	                'date_of_birth' => $data['new_student_dob'],
	                'gender' => $data['new_student_gender'], 
	                'status' => 'active',
	                'email_verified_at' => null, 
                    'enrollment_status' => 'active', 
                    'phone' => $data['new_student_phone'] ?? 'N/A',
                    'address' => $data['new_student_address'],
                    'city' => $data['new_student_city'],
                    'state' => $data['new_student_state'],
                    'country' => $data['new_student_country'],
                    'emergency_contact_name' => $data['new_student_emergency_contact_name'],
                    'emergency_contact_phone' => $data['new_student_emergency_contact_phone'],
                ];

                $student = $studentService->createStudent($studentData);
                
                if ($student->user->email) {
                    $student->user->notify(new NewStudentWelcomeNotification($student->user, $tempPassword));
                }

                $data['student_id'] = $student->id;
                
                $newStudentMessage = "New Student Created:\n" .
                                     "Name: {$data['new_student_first_name']} {$data['new_student_last_name']}\n" .
                                     "DOB: {$data['new_student_dob']}\n" .
                                     "Grade Level: {$data['new_student_grade_level']}\n" .
                                     "Email: " . ($data['new_student_email'] ?? 'N/A') . "\n\n";
                
                $data['parent_message'] = $newStudentMessage . ($data['parent_message'] ?? '');

                unset($data['is_new_student']);
                unset($data['new_student_first_name']);
                unset($data['new_student_last_name']);
                unset($data['new_student_email']);
                unset($data['new_student_dob']);
                unset($data['new_student_grade_level']);
                unset($data['new_student_phone']);
                unset($data['new_student_address']);
                unset($data['new_student_city']);
                unset($data['new_student_state']);
                unset($data['new_student_country']);
                unset($data['new_student_emergency_contact_name']);
                unset($data['new_student_emergency_contact_phone']);

            } catch (\Exception $e) {
                Notification::make()
                    ->danger()
                    ->title('Student Creation Failed')
                    ->body('An error occurred while creating the new student record. Please try again or contact support. Error: ' . $e->getMessage())
                    ->send();
                $this->halt();
            }
        }

        if ($data['student_id']) {
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
        $isNewStudent = Str::startsWith($this->getRecord()->student->student_id, 'TEMP-');

        return Notification::make()
            ->success()
            ->title($isNewStudent ? 'New Student Created & Linking Request Submitted' : 'Linking Request Submitted')
            ->body($isNewStudent ? 'A new student record has been created and a linking request has been sent to administrators for review. You will be notified once it is processed.' : 'Your request has been sent to administrators for review. You will be notified once it is processed.')
            ->duration(5000);
    }
}
