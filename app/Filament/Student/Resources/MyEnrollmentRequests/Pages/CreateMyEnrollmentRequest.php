<?php

namespace App\Filament\Student\Resources\MyEnrollmentRequests\Pages;

use App\Filament\Student\Resources\MyEnrollmentRequests\MyEnrollmentRequestResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMyEnrollmentRequest extends CreateRecord
{
    protected static string $resource = MyEnrollmentRequestResource::class;
}
