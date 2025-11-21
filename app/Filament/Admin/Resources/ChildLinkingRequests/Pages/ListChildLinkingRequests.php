<?php

namespace App\Filament\Admin\Resources\ChildLinkingRequests\Pages;

use App\Filament\Admin\Resources\ChildLinkingRequests\ChildLinkingRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListChildLinkingRequests extends ListRecords
{
    protected static string $resource = ChildLinkingRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
