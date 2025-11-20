<?php

namespace App\Filament\Parent\Resources\LinkChildren;

use UnitEnum;
use BackedEnum;
use App\Models\LinkChild;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use App\Models\ChildLinkingRequest;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Parent\Resources\LinkChildren\Pages\EditLinkChild;
use App\Filament\Parent\Resources\LinkChildren\Pages\ViewLinkChild;
use App\Filament\Parent\Resources\LinkChildren\Pages\CreateLinkChild;
use App\Filament\Parent\Resources\LinkChildren\Schemas\LinkChildForm;
use App\Filament\Parent\Resources\LinkChildren\Pages\ListLinkChildren;
use App\Filament\Parent\Resources\LinkChildren\Tables\LinkChildrenTable;
use App\Filament\Parent\Resources\LinkChildren\Schemas\LinkChildInfolist;

class LinkChildResource extends Resource
{
    protected static ?string $model = ChildLinkingRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserPlus;
    
    protected static string|UnitEnum|null $navigationGroup = 'Family';
    protected static ?string $navigationLabel = 'Link a Child';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Child Link Request';
    protected static ?string $pluralModelLabel = 'Child Link Requests';

    public static function getEloquentQuery(): Builder
    {
        $parent = Auth::user()->parent;
        
        return parent::getEloquentQuery()
            ->where('parent_id', $parent->id)
            ->with(['student.user', 'reviewer']);
    }

    public static function form(Schema $schema): Schema
    {
        return LinkChildForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LinkChildInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LinkChildrenTable::configure($table);
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
            'index' => ListLinkChildren::route('/'),
            'create' => CreateLinkChild::route('/create'),
            'view' => ViewLinkChild::route('/{record}'),
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

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return $record->status === 'pending';
    }
}
