<?php

namespace App\Filament\Parent\Resources\UpcomingClassResouces\Pages;

use App\Filament\Parent\Resources\UpcomingClassResouces\UpcomingClassResouceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUpcomingClassResouces extends ListRecords
{
    protected static string $resource = UpcomingClassResouceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
