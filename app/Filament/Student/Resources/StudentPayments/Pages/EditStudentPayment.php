<?php

namespace App\Filament\Student\Resources\StudentPayments\Pages;

use App\Filament\Student\Resources\StudentPayments\StudentPaymentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditStudentPayment extends EditRecord
{
    protected static string $resource = StudentPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
