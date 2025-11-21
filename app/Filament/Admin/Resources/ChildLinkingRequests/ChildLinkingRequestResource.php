<?php

namespace App\Filament\Admin\Resources\ChildLinkingRequests;

use App\Filament\Admin\Resources\ChildLinkingRequests\Pages\CreateChildLinkingRequest;
use App\Filament\Admin\Resources\ChildLinkingRequests\Pages\EditChildLinkingRequest;
use App\Filament\Admin\Resources\ChildLinkingRequests\Pages\ListChildLinkingRequests;
use App\Filament\Admin\Resources\ChildLinkingRequests\Pages\ViewChildLinkingRequest;
use App\Filament\Admin\Resources\ChildLinkingRequests\Schemas\ChildLinkingRequestForm;
use App\Filament\Admin\Resources\ChildLinkingRequests\Schemas\ChildLinkingRequestInfolist;
use App\Filament\Admin\Resources\ChildLinkingRequests\Tables\ChildLinkingRequestsTable;
use App\Models\ChildLinkingRequest;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ChildLinkingRequestResource extends Resource
{
    protected static ?string $model = ChildLinkingRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'User Management';
    protected static ?string $navigationLabel = 'Child Linking Requests';
    protected static ?int $navigationSort = 5;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['parent.user', 'student.user', 'reviewer']);
    }

    public static function form(Schema $schema): Schema
    {
        return ChildLinkingRequestForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ChildLinkingRequestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChildLinkingRequestsTable::configure($table);
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
            'index' => ListChildLinkingRequests::route('/'),
            'view' => ViewChildLinkingRequest::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
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
}
