<?php

namespace App\Filament\Admin\Resources\PaymentVerifications\Pages;

use App\Filament\Admin\Resources\PaymentVerifications\PaymentVerificationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPaymentVerification extends EditRecord
{
    protected static string $resource = PaymentVerificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
