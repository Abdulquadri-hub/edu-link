<?php

namespace App\Filament\Parent\Resources\ChildAttendances\Pages;

use App\Filament\Parent\Resources\ChildAttendances\ChildAttendanceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateChildAttendance extends CreateRecord
{
    protected static string $resource = ChildAttendanceResource::class;
}
