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
        return Notification::make()
            ->success()
            ->title('Linking Request Submitted')
            ->body('Your request has been sent to administrators for review. You will be notified once it is processed.')
            ->duration(5000);
    }
}
