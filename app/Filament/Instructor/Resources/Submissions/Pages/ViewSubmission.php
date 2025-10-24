<?php

namespace App\Filament\Instructor\Resources\Submissions\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Instructor\Resources\Submissions\SubmissionResource;

class ViewSubmission extends ViewRecord
{
    protected static string $resource = SubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('grade')
                ->visible(fn () => !$this->record->grade || !$this->record->grade->is_published)
                ->color('success')
                ->icon('heroicon-o-pencil-square')
                ->url(fn () => static::getResource()::getUrl('index')),
        ];
    }
}
