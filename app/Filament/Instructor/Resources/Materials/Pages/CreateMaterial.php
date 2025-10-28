<?php

namespace App\Filament\Instructor\Resources\Materials\Pages;

use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Instructor\Resources\Materials\MaterialResource;

class CreateMaterial extends CreateRecord
{
    protected static string $resource = MaterialResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array {
        $data['instructor_id'] = Auth::user()->instructor->id;
        $data['uploaded_at'] = now();
        return $data;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Material uploaded successfully')
            ->body('You can publish it now or save as draft for later.');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
