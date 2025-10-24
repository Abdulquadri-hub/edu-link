<?php

namespace App\Filament\Instructor\Resources\ClassSessions\Pages;

use App\Filament\Instructor\Resources\ClassSessions\ClassSessionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditClassSession extends EditRecord
{
    protected static string $resource = ClassSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
