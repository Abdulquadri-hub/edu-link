<?php

namespace App\Filament\Admin\Resources\StudentPromotions\Pages;

use App\Filament\Admin\Resources\StudentPromotions\StudentPromotionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditStudentPromotion extends EditRecord
{
    protected static string $resource = StudentPromotionResource::class;

    
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // Only allow editing pending promotions
    protected function authorizeAccess(): void
    {
        parent::authorizeAccess();

        $record = $this->getRecord();
        
        if ($record->status !== 'pending') {
            abort(403, 'Cannot edit approved/rejected promotions');
        }
    }
}
