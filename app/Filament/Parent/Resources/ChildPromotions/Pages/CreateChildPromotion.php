<?php

namespace App\Filament\Parent\Resources\ChildPromotions\Pages;

use App\Filament\Parent\Resources\ChildPromotions\ChildPromotionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateChildPromotion extends CreateRecord
{
    protected static string $resource = ChildPromotionResource::class;
}
