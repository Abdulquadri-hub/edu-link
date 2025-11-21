<?php

namespace App\Filament\Admin\Resources\ChildLinkingRequests\Pages;

use App\Filament\Admin\Resources\ChildLinkingRequests\ChildLinkingRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditChildLinkingRequest extends EditRecord
{
    protected static string $resource = ChildLinkingRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
