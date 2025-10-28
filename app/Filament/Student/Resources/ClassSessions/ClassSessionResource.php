<?php

namespace App\Filament\Student\Resources\ClassSessions;

use BackedEnum;
use Filament\Tables\Table;
use App\Models\ClassSession;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Student\Resources\ClassSessions\Schemas\ClassSessionInfolist;
use App\Filament\Student\Resources\ClassSessions\Pages\EditClassSession;
use App\Filament\Student\Resources\ClassSessions\Pages\ViewClassSession;
use App\Filament\Student\Resources\ClassSessions\Pages\ListClassSessions;
use App\Filament\Student\Resources\ClassSessions\Pages\CreateClassSession;
use App\Filament\Student\Resources\ClassSessions\Schemas\ClassSessionForm;
use App\Filament\Student\Resources\ClassSessions\Tables\ClassSessionsTable;
use UnitEnum;

class ClassSessionResource extends Resource
{
    protected static ?string $model = ClassSession::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CalendarDays;
    protected static string|UnitEnum|null $navigationGroup = 'Learning';
    protected static ?string $navigationLabel = 'Class Schedule';
    protected static ?int $navigationSort = 4;

    public static function getEloquentQuery(): Builder
    {
        $student = Auth::user()->student;
        
        return parent::getEloquentQuery()
            ->whereHas('course.enrollments', function ($query) use ($student) {
                $query->where('student_id', $student->id)
                      ->where('status', 'active');
            })
            ->whereIn('status', ['scheduled', 'in-progress', 'completed'])
            ->with(['course', 'instructor.user']);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return ClassSessionInfolist::configure($schema);
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
            'view' => ViewClassSession::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $student = Auth::user()->student;
        
        $count = static::getModel()::whereHas('course.enrollments', function ($query) use ($student) {
            $query->where('student_id', $student->id)->where('status', 'active');
        })
        ->where('status', 'scheduled')
        ->where('scheduled_at', '>', now())
        ->where('scheduled_at', '<', now()->addDay())
        ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }
}
