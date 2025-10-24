<?php

namespace App\Filament\Instructor\Resources\Submissions\Pages;

use App\Filament\Instructor\Resources\Submissions\SubmissionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSubmission extends CreateRecord
{
    protected static string $resource = SubmissionResource::class;
}
