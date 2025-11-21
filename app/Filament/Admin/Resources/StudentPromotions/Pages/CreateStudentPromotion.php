<?php

namespace App\Filament\Admin\Resources\StudentPromotions\Pages;

use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Admin\Resources\StudentPromotions\StudentPromotionResource;

class CreateStudentPromotion extends CreateRecord
{
    protected static string $resource = StudentPromotionResource::class;

     protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['promoted_by'] = Auth::id();
        $data['status'] = 'pending';
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Promotion created successfully';
    }
}
