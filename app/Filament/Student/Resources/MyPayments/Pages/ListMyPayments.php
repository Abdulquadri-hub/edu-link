<?php

namespace App\Filament\Parent\Resources\Payments\Pages;

use App\Filament\Parent\Resources\Payments\PaymentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

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
