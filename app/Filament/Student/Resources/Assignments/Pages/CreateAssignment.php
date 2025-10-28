<?php

namespace App\Filament\Student\Resources\Assignments\Pages;

use App\Filament\Student\Resources\Assignments\AssignmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAssignment extends CreateRecord
{
    protected static string $resource = AssignmentResource::class;
}
