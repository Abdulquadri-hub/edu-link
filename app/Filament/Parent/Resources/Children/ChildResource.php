<?php

namespace App\Filament\Parent\Resources\Children;

use BackedEnum;
use App\Models\Student;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Parent\Resources\Children\Schemas\ChildForm;
use App\Filament\Parent\Resources\Children\Schemas\ChildInfolist;
use App\Filament\Parent\Resources\Children\Pages\ListChildren;
use App\Filament\Parent\Resources\Children\Pages\ViewChild;
use App\Filament\Parent\Resources\Children\Tables\ChildrenTable;

use UnitEnum;

class ChildResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Users;
    protected static string|UnitEnum|null $navigationGroup = 'Family';
    protected static ?string $navigationLabel = 'My Children';
    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        $parent = Auth::user()->parent;
        
        return parent::getEloquentQuery()
            ->whereHas('parents', function ($query) use ($parent) {
                $query->where('student_parent.parent_id', $parent->id);
            })
            ->with(['user', 'enrollments.course', 'parents']);
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
        return ChildInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChildrenTable::configure($table);
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
            'index' => ListChildren::route('/'),
            'view' => ViewChild::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $parent = Auth::user()->parent;
        $count = $parent->children()->count();
        return (string) $count;
    }
}
