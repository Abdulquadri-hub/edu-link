<?php

namespace App\Filament\Instructor\Resources\ClassSessions\Pages;

use App\Events\ClassScheduled;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Instructor\Resources\ClassSessions\ClassSessionResource;

class CreateClassSession extends CreateRecord
{
    protected static string $resource = ClassSessionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['instructor_id'] = Auth::user()->instructor->id;
        return $data;
    }

    protected function afterCreate(): void {
        event(new ClassScheduled($this->record));
    }
}
