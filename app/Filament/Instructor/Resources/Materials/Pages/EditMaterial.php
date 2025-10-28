<?php

namespace App\Filament\Instructor\Resources\Materials\Pages;

use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Instructor\Resources\Materials\MaterialResource;

class EditMaterial extends EditRecord
{
    protected static string $resource = MaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

     protected function mutateFormDataBeforeCreate(array $data): array {
        $data['instructor_id'] = Auth::user()->instructor->id;
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
