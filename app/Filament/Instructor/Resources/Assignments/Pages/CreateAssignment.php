<?php

namespace App\Filament\Instructor\Resources\Assignments\Pages;

use App\Events\AssignmentCreated;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Instructor\Resources\Assignments\AssignmentResource;

class CreateAssignment extends CreateRecord
{
    protected static string $resource = AssignmentResource::class;


    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['instructor_id'] = Auth::user()->instructor->id;
        return $data;
    }

    protected function afterCreate(): void {
        event(new AssignmentCreated($this->record));
    }
}
