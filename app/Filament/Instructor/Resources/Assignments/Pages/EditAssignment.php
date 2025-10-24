<?php

namespace App\Filament\Instructor\Resources\Assignments\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\ForceDeleteAction;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Instructor\Resources\Assignments\AssignmentResource;

class EditAssignment extends EditRecord
{
    protected static string $resource = AssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['instructor_id'] = Auth::user()->instructor->id;
        return $data;
    }
    
}
