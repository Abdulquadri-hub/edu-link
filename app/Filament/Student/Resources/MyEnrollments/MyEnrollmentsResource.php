<?php

namespace App\Filament\Student\Resources\MyEnrollments;

use App\Models\Enrollment;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use BackedEnum;
use Filament\Resources\Pages\ListRecords;

class MyEnrollmentsResource extends Resource
{
    protected static ?string $model = Enrollment::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-collection';

    public static function getNavigationGroup(): ?string
    {
        return 'My Learning';
    }

    public static function table(Table $table): Table
    {
        return (new \App\Filament\Student\Resources\MyEnrollments\Tables\MyEnrollmentsTable)::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRecords::route('/'),
        ];
    }
}
