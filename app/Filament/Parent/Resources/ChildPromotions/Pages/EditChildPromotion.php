<?php

namespace App\Filament\Parent\Resources\ChildPromotions\Pages;

use App\Filament\Parent\Resources\ChildPromotions\ChildPromotionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditChildPromotion extends EditRecord
{
    protected static string $resource = ChildPromotionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
