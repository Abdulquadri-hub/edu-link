<?php

namespace App\Filament\Admin\Resources\PaymentVerifications\Pages;

use App\Filament\Admin\Resources\PaymentVerifications\PaymentVerificationResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePaymentVerification extends CreateRecord
{
    protected static string $resource = PaymentVerificationResource::class;
}
