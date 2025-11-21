<?php

namespace App\Filament\Student\Resources\AvailableCourses\Pages;

use App\Filament\Student\Resources\AvailableCourses\AvailableCourseResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAvailableCourse extends ViewRecord
{
    protected static string $resource = AvailableCourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
