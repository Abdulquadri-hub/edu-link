<?php

namespace App\Filament\Parent\Resources\ChildAttendances;

use UnitEnum;
use BackedEnum;
use App\Models\Attendance;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Parent\Resources\ChildAttendances\Pages\EditChildAttendance;
use App\Filament\Parent\Resources\ChildAttendances\Pages\ViewChildAttendance;
use App\Filament\Parent\Resources\ChildAttendances\Pages\ListChildAttendances;
use App\Filament\Parent\Resources\ChildAttendances\Pages\CreateChildAttendance;
use App\Filament\Parent\Resources\ChildAttendances\Schemas\ChildAttendanceForm;
use App\Filament\Parent\Resources\ChildAttendances\Tables\ChildAttendancesTable;
use App\Filament\Parent\Resources\ChildAttendances\Schemas\ChildAttendanceInfolist;

class ChildAttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Academic';
    protected static ?string $navigationLabel = 'Attendance';
    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        $parent = Auth::user()->parent;
        
        return parent::getEloquentQuery()
            ->whereHas('student.parents', function ($query) use ($parent) {
                $query->where('student_parent.parent_id', $parent->id)
                      ->where('can_view_attendance', true);
            })
            ->with(['student.user', 'classSession.course', 'classSession.instructor.user']);
    }

    
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return ChildAttendanceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChildAttendancesTable::configure($table);
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
            'index' => ListChildAttendances::route('/'),
            'view' => ViewChildAttendance::route('/{record}'),
        ];
    }

}
