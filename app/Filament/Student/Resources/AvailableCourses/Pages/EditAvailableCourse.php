<?php

namespace App\Filament\Student\Resources\AvailableCourses\Pages;

use App\Filament\Student\Resources\AvailableCourses\AvailableCourseResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAvailableCourse extends EditRecord
{
    protected static string $resource = AvailableCourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
