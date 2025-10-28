<?php

namespace App\Filament\Parent\Resources\ChildGrades\Pages;

use App\Filament\Parent\Resources\ChildGrades\ChildGradeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateChildGrade extends CreateRecord
{
    protected static string $resource = ChildGradeResource::class;
}
