<?php

namespace App\Filament\Parent\Resources\LinkChildren\Pages;

use App\Filament\Parent\Resources\LinkChildren\LinkChildResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditLinkChild extends EditRecord
{
    protected static string $resource = LinkChildResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
