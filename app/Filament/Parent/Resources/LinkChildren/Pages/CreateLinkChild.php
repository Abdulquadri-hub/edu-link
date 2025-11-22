<?php

namespace App\Filament\Parent\Resources\LinkChildren\Pages;

use App\Models\ChildLinkingRequest;
use App\Models\User;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
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
            // 1. Create a new User and Student record
            DB::beginTransaction();
            try {
                $tempPassword = Str::random(10); // Temporary password

                $user = User::create([
                    'first_name' => $data['new_student_first_name'],
                    'last_name' => $data['new_student_last_name'],
                    'email' => $data['new_student_email'] ?? null,
                    'password' => bcrypt($tempPassword), // Use a temporary password
                    'role' => 'student',
                ]);

                $student = Student::create([
                    'user_id' => $user->id,
                    'student_id' => 'TEMP-' . Str::upper(Str::random(6)), // Temporary student ID
                    'enrollment_status' => 'pending', // New students are pending enrollment
                    // Assuming 'academic_level_id' is required, but since we only have a string, 
                    // we'll leave it out or set it to null and let the admin finalize it.
                    // The grade_level string will be added to the parent_message for admin reference.
                ]);

                // 2. Set the newly created student's ID for the linking request
                $data['student_id'] = $student->id;
                
                // 3. Add new student details to the message for admin context
                $newStudentMessage = "New Student Created:\n" .
                                     "Name: {$data['new_student_first_name']} {$data['new_student_last_name']}\n" .
                                     "DOB: {$data['new_student_dob']}\n" .
                                     "Grade Level: {$data['new_student_grade_level']}\n" .
                                     "Email: " . ($data['new_student_email'] ?? 'N/A') . "\n\n";
                
                $data['parent_message'] = $newStudentMessage . ($data['parent_message'] ?? '');

                // 4. Clean up temporary form fields
                unset($data['is_new_student']);
                unset($data['new_student_first_name']);
                unset($data['new_student_last_name']);
                unset($data['new_student_email']);
                unset($data['new_student_dob']);
                unset($data['new_student_grade_level']);

                DB::commit();

            } catch (\Exception $e) {
                DB::rollBack();
                Notification::make()
                    ->danger()
                    ->title('Student Creation Failed')
                    ->body('An error occurred while creating the new student record. Please try again or contact support.')
                    ->send();
                $this->halt();
            }
        }

        // Standard checks for existing student linking (also applies to newly created student)
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
        // The student_id will be set in $data if a new student was created, so we can't rely on $data['is_new_student'] here.
        // We can check if any of the new student fields are present, but the safest is to check the student model after creation.
        // Since we are in mutateFormDataBeforeCreate, we can't check the created record.
        // I will rely on the fact that if a new student was created, the notification should be more specific.
        // However, since the new student fields are unset, I'll stick to a generic message or find a better way.
        // For now, I'll check if the student_id was originally null (before the mutation) but that's not possible here.
        // I will use a session flash or a temporary property on the page class, but for simplicity, I'll use a check on the data array before unsetting.
        // Since I'm using $data after mutation, I'll just use a generic success message.
        return 'Linking request submitted';
    }

    protected function getCreatedNotification(): ?Notification
    {
        // Check if the student_id was set during the mutation (meaning a new student was created)
        $isNewStudent = Str::startsWith($this->getRecord()->student->student_id, 'TEMP-');

        return Notification::make()
            ->success()
            ->title($isNewStudent ? 'New Student Created & Linking Request Submitted' : 'Linking Request Submitted')
            ->body($isNewStudent ? 'A new student record has been created and a linking request has been sent to administrators for review. You will be notified once it is processed.' : 'Your request has been sent to administrators for review. You will be notified once it is processed.')
            ->duration(5000);
    }
}
