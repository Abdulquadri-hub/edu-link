<?php

namespace App\Filament\Instructor\Resources\Courses\Pages;

use App\Filament\Instructor\Resources\Courses\CourseResource;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCourse extends ViewRecord
{
    protected static string $resource = CourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
