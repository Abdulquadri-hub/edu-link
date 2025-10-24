<?php

namespace App\Filament\Instructor\Resources\Assignments;

use App\Filament\Instructor\Resources\Assignments\Pages\CreateAssignment;
use App\Filament\Instructor\Resources\Assignments\Pages\EditAssignment;
use App\Filament\Instructor\Resources\Assignments\Pages\ListAssignments;
use App\Filament\Instructor\Resources\Assignments\Schemas\AssignmentForm;
use App\Filament\Instructor\Resources\Assignments\Tables\AssignmentsTable;
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
    protected static string|UnitEnum|null $navigationGroup = 'Teaching';
    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'Assignment';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('instructor_id', Auth::user()->instructor->id)
            ->with(['course', 'submissions']);
    }

    public static function form(Schema $schema): Schema
    {
        return AssignmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssignmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\SubmissionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAssignments::route('/'),
            'create' => CreateAssignment::route('/create'),
            'edit' => EditAssignment::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }


}
