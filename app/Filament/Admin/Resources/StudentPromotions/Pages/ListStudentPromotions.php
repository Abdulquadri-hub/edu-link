<?php

namespace App\Filament\Admin\Resources\StudentPromotions\Pages;

use App\Filament\Admin\Resources\StudentPromotions\StudentPromotionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStudentPromotions extends ListRecords
{
    protected static string $resource = StudentPromotionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Promote Student')
                ->icon('heroicon-o-arrow-trending-up'),
        ];
    }
}
