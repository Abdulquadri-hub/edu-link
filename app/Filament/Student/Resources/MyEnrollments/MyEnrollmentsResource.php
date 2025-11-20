<?php

namespace App\Filament\Student\Resources\MyEnrollments;

use BackedEnum;
use App\Models\Enrollment;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Student\Resources\MyEnrollments\Pages\ListMyEnrollments;
use App\Filament\Student\Resources\MyEnrollments\Tables\MyEnrollmentsTable;

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
        return (new MyEnrollmentsTable)::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMyEnrollments::route('/'),
        ];
    }
}
