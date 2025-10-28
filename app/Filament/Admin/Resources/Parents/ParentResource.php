<?php

namespace App\Filament\Admin\Resources\Parents;

use App\Filament\Admin\Resources\Parents\Pages\CreateParent;
use App\Filament\Admin\Resources\Parents\Pages\EditParent;
use App\Filament\Admin\Resources\Parents\Pages\ListParents;
use App\Filament\Admin\Resources\Parents\Schemas\ParentForm;
use App\Filament\Admin\Resources\Parents\Tables\ParentsTable;
use App\Models\ParentModel;
use BackedEnum;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Relations\Relation;

class ParentResource extends Resource
{
    protected static ?string $model = ParentModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserPlus;
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationLabel = 'Parents';
    protected static ?string $pluralLabel = 'Parents';

    public static function getNavigationGroup(): ?string
    {
        return 'User Management';
    }

    public static function form(Schema $schema): Schema
    {
        return ParentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ParentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ChildrenRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListParents::route('/'),
            'create' => CreateParent::route('/create'),
            'edit' => EditParent::route('/{record}/edit'),
        ];
    }
}
