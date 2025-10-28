<?php

namespace App\Filament\Student\Resources\Grades\Pages;

use App\Filament\Student\Resources\Grades\GradeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGrade extends CreateRecord
{
    protected static string $resource = GradeResource::class;
}
