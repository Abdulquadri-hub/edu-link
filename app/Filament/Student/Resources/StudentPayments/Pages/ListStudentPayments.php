<?php

namespace App\Filament\Student\Resources\StudentPayments\Pages;

use App\Filament\Student\Resources\StudentPayments\StudentPaymentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStudentPayments extends ListRecords
{
    protected static string $resource = StudentPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Upload Payment')
                ->icon('heroicon-o-plus')
                ->modalHeading('Upload Payment Receipt')
                ->modalWidth('3xl'),
        ];
    }
}
