<?php

namespace App\Filament\Instructor\Resources\ClassSessions;

use App\Filament\Instructor\Resources\ClassSessions\Pages\CreateClassSession;
use App\Filament\Instructor\Resources\ClassSessions\Pages\EditClassSession;
use App\Filament\Instructor\Resources\ClassSessions\Pages\ListClassSessions;
use App\Filament\Instructor\Resources\ClassSessions\Schemas\ClassSessionForm;
use App\Filament\Instructor\Resources\ClassSessions\Tables\ClassSessionsTable;
use App\Models\ClassSession;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ClassSessionResource extends Resource
{
    protected static ?string $model = ClassSession::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CalendarDays;
    protected static ?string $navigationLabel = 'Class Sessions';
    protected static ?int $navigationSort = 2;

    protected static string|UnitEnum|null $navigationGroup = 'Teaching';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('instructor_id', Auth::user()->instructor->id)
            ->with(['course', 'attendances']);
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
            //RelationManagers\AttendancesRelationManager::class,
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
