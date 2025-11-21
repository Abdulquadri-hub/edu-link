<?php

namespace App\Filament\Student\Resources\AvailableCourses\Pages;

use App\Filament\Student\Resources\AvailableCourses\AvailableCourseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAvailableCourse extends CreateRecord
{
    protected static string $resource = AvailableCourseResource::class;
}
