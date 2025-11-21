<?php

namespace App\Filament\Admin\Resources\PaymentVerifications\Pages;

use App\Filament\Admin\Resources\PaymentVerifications\PaymentVerificationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPaymentVerification extends ViewRecord
{
    protected static string $resource = PaymentVerificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
