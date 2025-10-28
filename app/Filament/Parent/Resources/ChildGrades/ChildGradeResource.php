<?php

namespace App\Filament\Parent\Resources\ChildGrades;

use App\Filament\Parent\Resources\ChildGrades\Pages\CreateChildGrade;
use App\Filament\Parent\Resources\ChildGrades\Pages\EditChildGrade;
use App\Filament\Parent\Resources\ChildGrades\Pages\ListChildGrades;
use App\Filament\Parent\Resources\ChildGrades\Pages\ViewChildGrade;
use App\Filament\Parent\Resources\ChildGrades\Schemas\ChildGradeForm;
use App\Filament\Parent\Resources\ChildGrades\Schemas\ChildGradeInfolist;
use App\Filament\Parent\Resources\ChildGrades\Tables\ChildGradesTable;
use App\Models\ChildGrade;
use App\Models\Grade;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ChildGradeResource extends Resource
{
    protected static ?string $model = Grade::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::AcademicCap;

    protected static string|UnitEnum|null $navigationGroup = 'Academic';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Grades';

    public static function getEloquentQuery(): Builder
    {
        $parent = Auth::user()->parent;

        return parent::getEloquentQuery()
            ->whereHas('submission.student.parents', function ($query) use ($parent) {
                $query->where('student_parent.parent_id', $parent->id);
                
                // Check can_view_grades permission
                $query->where(function ($q) use ($parent) {
                    $q->where('can_view_grades', true);
                });
            })
            ->where('is_published', true)
            ->with(['submission.student.user', 'submission.assignment.course', 'instructor.user']);
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
        return ChildGradeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChildGradesTable::configure($table);
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
            'index' => ListChildGrades::route('/'),
            'create' => CreateChildGrade::route('/create'),
            'view' => ViewChildGrade::route('/{record}'),
            'edit' => EditChildGrade::route('/{record}/edit'),
        ];
    }
}
