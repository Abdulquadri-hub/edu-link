<?php

namespace App\Filament\Student\Resources\Grades;

use UnitEnum;
use BackedEnum;
use App\Models\Grade;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Student\Resources\Grades\Pages\EditGrade;
use App\Filament\Student\Resources\Grades\Pages\ViewGrade;
use App\Filament\Student\Resources\Grades\Pages\ListGrades;
use App\Filament\Student\Resources\Grades\Pages\CreateGrade;
use App\Filament\Student\Resources\Grades\Schemas\GradeForm;
use App\Filament\Student\Resources\Grades\Tables\GradesTable;
use App\Filament\Student\Resources\Grades\Schemas\GradeInfolist;

class GradeResource extends Resource
{
    protected static ?string $model = Grade::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::AcademicCap;
    protected static string|UnitEnum|null $navigationGroup = 'Learning';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'My Grades';

    public static function getEloquentQuery(): Builder 
    {
        $student = Auth::user()->student;
        
        return parent::getEloquentQuery()
            ->whereHas('submission', function ($query) use ($student) {
                $query->where('student_id', $student->id);
            })
            ->where('is_published', true)
            ->with(['submission.assignment.course', 'instructor.user']);
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

    public static function table(Table $table): Table
    {
        return GradesTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return GradeInfolist::configure($schema);
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
            'index' => ListGrades::route('/'),
            'view' => ViewGrade::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $student = Auth::user()->student;
        
        $count = static::getModel()::whereHas('submission', function ($query) use ($student) {
            $query->where('student_id', $student->id);
        })
        ->where('is_published', true)
        ->where('published_at', '>', now()->subWeek())
        ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
