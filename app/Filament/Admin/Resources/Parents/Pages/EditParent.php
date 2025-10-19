<?php

namespace App\Filament\Admin\Resources\Parents\Pages;

use App\Filament\Admin\Resources\Parents\ParentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditParent extends EditRecord
{
    protected static string $resource = ParentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
