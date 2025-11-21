<?php

namespace App\Filament\Parent\Resources\ChildPromotions\Pages;

use App\Filament\Parent\Resources\ChildPromotions\ChildPromotionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListChildPromotions extends ListRecords
{
    protected static string $resource = ChildPromotionResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
