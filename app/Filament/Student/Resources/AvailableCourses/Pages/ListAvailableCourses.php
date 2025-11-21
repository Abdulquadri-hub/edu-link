<?php

namespace App\Filament\Student\Resources\AvailableCourses\Pages;

use App\Filament\Student\Resources\AvailableCourses\AvailableCourseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAvailableCourses extends ListRecords
{
    protected static string $resource = AvailableCourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
