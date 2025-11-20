<?php

namespace App\Filament\Student\Resources\AvailableCourses\Pages;

use App\Filament\Student\Resources\AvailableCourses\AvailableCoursesResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAvailableCourses extends CreateRecord
{
    protected static string $resource = AvailableCoursesResource::class;
}
