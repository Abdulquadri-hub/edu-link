<?php

namespace App\Filament\Student\Resources\StudentPayments\Pages;

use App\Models\User;
use App\Models\EnrollmentRequest;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Notifications\NewPaymentUploadedNotification;
use App\Filament\Student\Resources\StudentPayments\StudentPaymentResource;

class CreateStudentPayment extends CreateRecord
{
    protected static string $resource = StudentPaymentResource::class;

        protected function mutateFormDataBeforeCreate(array $data): array
    {
        $student = Auth::user()->student;
        
        $data['student_id'] = $student->id;
        $data['parent_id'] = null; // Student payment, not from parent
        $data['status'] = 'pending';
        
        // Store the original filename
        if (isset($data['receipt_path'])) {
            $data['receipt_filename'] = basename($data['receipt_path']);
        }
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Payment uploaded successfully';
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Payment Receipt Uploaded')
            ->body('Your payment has been submitted for verification. You will be notified once it is processed.')
            ->duration(5000);
    }

   protected function afterCreate(): void
    {
        // Update related enrollment request if exists
        $student = Auth::user()->student;
        
        $enrollmentRequest = EnrollmentRequest::where('student_id', $student->id)
            ->where('course_id', $this->record->course_id)
            ->whereIn('status', ['pending', 'payment_pending'])
            ->first();
        
        if ($enrollmentRequest) {
            $enrollmentRequest->update(['status' => 'payment_pending']);
        }
        
        // Send notification to admin about new payment
        $admins = \App\Models\User::where('user_type', 'admin')->get();
        
        foreach ($admins as $admin) {
            $admin->notify(new NewPaymentUploadedNotification($this->record));
        }
    }
}
