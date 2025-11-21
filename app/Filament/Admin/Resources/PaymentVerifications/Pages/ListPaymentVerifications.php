<?php

namespace App\Filament\Admin\Resources\PaymentVerifications\Pages;

use App\Filament\Admin\Resources\PaymentVerifications\PaymentVerificationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPaymentVerifications extends ListRecords
{
    protected static string $resource = PaymentVerificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
