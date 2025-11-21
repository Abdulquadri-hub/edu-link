<?php

namespace App\Filament\Student\Resources\MyEnrollmentRequests\Pages;

use App\Filament\Student\Resources\MyEnrollmentRequests\MyEnrollmentRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMyEnrollmentRequest extends EditRecord
{
    protected static string $resource = MyEnrollmentRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
