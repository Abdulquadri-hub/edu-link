<?php

namespace App\Filament\Student\Resources\MyEnrollmentRequests\Pages;

use App\Filament\Student\Resources\MyEnrollmentRequests\MyEnrollmentRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMyEnrollmentRequests extends ListRecords
{
    protected static string $resource = MyEnrollmentRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
