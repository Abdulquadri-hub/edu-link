<?php

namespace App\Filament\Parent\Resources\Payments\Pages;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Parent\Resources\Payments\PaymentResource;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $parent = Auth::user()->parent;
        $data['parent_id'] = $parent->id;
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
        Notification::make()
            ->title('New Payment Receipt')
            ->body("A new payment receipt has been uploaded by {$this->record->parent->user->full_name}")
            ->sendToDatabase(User::where('user_type', 'admin')->get());
    }
}
