<?php

namespace App\Filament\Student\Resources\MyEnrollmentRequests\Pages;

use App\Filament\Student\Resources\MyEnrollmentRequests\MyEnrollmentRequestResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMyEnrollmentRequest extends ViewRecord
{
    protected static string $resource = MyEnrollmentRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
