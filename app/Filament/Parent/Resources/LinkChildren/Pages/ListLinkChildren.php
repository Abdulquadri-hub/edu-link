<?php

namespace App\Filament\Parent\Resources\LinkChildren\Pages;

use App\Filament\Parent\Resources\LinkChildren\LinkChildResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLinkChildren extends ListRecords
{
    protected static string $resource = LinkChildResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Link New Child')
                ->icon('heroicon-o-plus')
                ->modalHeading('Request to Link a Child')
                ->modalWidth('2xl'),
        ];
    }
}
