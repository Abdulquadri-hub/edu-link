<?php

namespace App\Filament\Instructor\Resources\Students\Pages;

use App\Filament\Instructor\Resources\Students\StudentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;
}
