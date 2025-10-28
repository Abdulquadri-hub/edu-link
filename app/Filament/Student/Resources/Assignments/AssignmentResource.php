<?php

namespace App\Filament\Student\Resources\Assignments;

use App\Filament\Student\Resources\Assignments\Pages\CreateAssignment;
use App\Filament\Student\Resources\Assignments\Pages\EditAssignment;
use App\Filament\Student\Resources\Assignments\Pages\ListAssignments;
use App\Filament\Student\Resources\Assignments\Pages\ViewAssignment;
use App\Filament\Student\Resources\Assignments\Schemas\AssignmentForm;
use App\Filament\Student\Resources\Assignments\Schemas\AssignmentInfolist;
use App\Filament\Student\Resources\Assignments\Tables\AssignmentsTable;
use App\Models\Assignment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class AssignmentResource extends Resource
{
    protected static ?string $model = Assignment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentText;
    protected static string|UnitEnum|null $navigationGroup = 'Learning';
    protected static ?string $navigationLabel = 'Assignments';
    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        $student = Auth::user()->student;
        return parent::getEloquentQuery()
            ->whereHas('course.enrollments', function ($query) use ($student) {
                $query->where('student_id', $student->id)
                      ->where('status', 'active');
            })
            ->where('status', 'published')
            ->with(['course', 'submissions' => function ($query) use ($student) {
                $query->where('student_id', $student->id);
            }]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return AssignmentsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AssignmentInfolist::configure($schema);
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
            'index' => ListAssignments::route('/'),
            'view' => ViewAssignment::route('/{record}'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        $student = Auth::user()->student;
        
        $count = static::getModel()::whereHas('course.enrollments', function ($query) use ($student) {
            $query->where('student_id', $student->id)->where('status', 'active');
        })
        ->where('status', 'published')
        ->where('due_at', '>', now())
        ->whereDoesntHave('submissions', function ($q) use ($student) {
            $q->where('student_id', $student->id);
        })
        ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
    
}
