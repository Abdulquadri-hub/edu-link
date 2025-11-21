<?php

namespace App\Filament\Parent\Resources\ChildPromotions\Pages;

use App\Filament\Parent\Resources\ChildPromotions\ChildPromotionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewChildPromotion extends ViewRecord
{
    protected static string $resource = ChildPromotionResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
