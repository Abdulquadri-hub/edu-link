<?php

namespace App\Filament\Parent\Resources\Children\Pages;

use App\Filament\Parent\Resources\Children\ChildResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewChild extends ViewRecord
{
    protected static string $resource = ChildResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // EditAction::make(),
        ];
    }
}
