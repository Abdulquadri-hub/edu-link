<?php

namespace App\Filament\Admin\Resources\StudentPromotions\Pages;

use App\Filament\Admin\Resources\StudentPromotions\StudentPromotionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewStudentPromotion extends ViewRecord
{
    protected static string $resource = StudentPromotionResource::class;

    protected function getHeaderActions(): array
    {
        return [
           EditAction::make()
                ->visible(fn () => $this->getRecord()->status === 'pending'),
        ];
    }
}
