<?php

namespace App\Filament\Parent\Resources\ParentAssignments;

use UnitEnum;
use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use App\Models\ParentAssignment;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Parent\Resources\ParentAssignments\Pages\ListParentAssignments;
use App\Filament\Parent\Resources\ParentAssignments\Pages\CreateParentAssignment;
use App\Filament\Parent\Resources\ParentAssignments\Pages\EditParentAssignment;
use App\Filament\Parent\Resources\ParentAssignments\Pages\ViewParentAssignment;
use App\Filament\Parent\Resources\ParentAssignments\Schemas\ParentAssignmentForm;
use App\Filament\Parent\Resources\ParentAssignments\Schemas\ParentAssignmentInfolist;
use App\Filament\Parent\Resources\ParentAssignments\Tables\ParentAssignmentsTable;

class ParentAssignmentResource extends Resource
{
    protected static ?string $model = ParentAssignment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentArrowUp;
    
    protected static string|UnitEnum|null $navigationGroup = 'Academic';
    protected static ?string $navigationLabel = 'Upload Assignments';
    protected static ?int $navigationSort = 4;

    public static function getEloquentQuery(): Builder
    {
        $parent = Auth::user()->parent;
        
        return parent::getEloquentQuery()
            ->where('parent_id', $parent->id)
            ->with(['student.user', 'assignment.course', 'submission.grade']);
    }

    public static function form(Schema $schema): Schema
    {
        return ParentAssignmentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ParentAssignmentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ParentAssignmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListParentAssignments::route('/'),
            'create' => CreateParentAssignment::route('/create'),
            'edit' => EditParentAssignment::route('/{record}/edit'),
            'view' => ViewParentAssignment::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $parent = Auth::user()->parent;
        
        $count = static::getModel()::where('parent_id', $parent->id)
            ->where('status', 'pending')
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}