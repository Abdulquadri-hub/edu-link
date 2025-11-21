<?php

namespace App\Filament\Parent\Resources\LinkChildren\Pages;

use App\Filament\Parent\Resources\LinkChildren\LinkChildResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLinkChild extends ViewRecord
{
    protected static string $resource = LinkChildResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // EditAction::make(),
        ];
    }
}
