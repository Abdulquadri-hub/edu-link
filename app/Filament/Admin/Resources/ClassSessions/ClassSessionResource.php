<?php

namespace App\Filament\Admin\Resources\ClassSessions;

use App\Filament\Admin\Resources\ClassSessions\Pages\CreateClassSession;
use App\Filament\Admin\Resources\ClassSessions\Pages\EditClassSession;
use App\Filament\Admin\Resources\ClassSessions\Pages\ListClassSessions;
use App\Filament\Admin\Resources\ClassSessions\Schemas\ClassSessionForm;
use App\Filament\Admin\Resources\ClassSessions\Tables\ClassSessionsTable;
use App\Models\ClassSession;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ClassSessionResource extends Resource
{
    protected static ?string $model = ClassSession::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CalendarDays;

    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'Class Sessions';

     public static function getNavigationGroup(): ?string
    {
        return 'Academic';
    }

    public static function form(Schema $schema): Schema
    {
        return ClassSessionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClassSessionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClassSessions::route('/'),
            'create' => CreateClassSession::route('/create'),
            'edit' => EditClassSession::route('/{record}/edit'),
        ];
    }
}
