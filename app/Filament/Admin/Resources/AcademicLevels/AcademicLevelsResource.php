<?php

namespace App\Filament\Admin\Resources\AcademicLevels;

use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\AcademicLevel;
use App\Models\AcademicLevels;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use App\Filament\Admin\Resources\AcademicLevels\Pages\EditAcademicLevels;
use App\Filament\Admin\Resources\AcademicLevels\Pages\ListAcademicLevels;
use App\Filament\Admin\Resources\AcademicLevels\Pages\ViewAcademicLevels;
use App\Filament\Admin\Resources\AcademicLevels\Pages\CreateAcademicLevels;
use App\Filament\Admin\Resources\AcademicLevels\Schemas\AcademicLevelsForm;
use App\Filament\Admin\Resources\AcademicLevels\Tables\AcademicLevelsTable;
use App\Filament\Admin\Resources\AcademicLevels\Schemas\AcademicLevelsInfolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class AcademicLevelsResource extends Resource
{
    protected static ?string $model = AcademicLevel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::AcademicCap;

    protected static ?string $navigationLabel = 'Academic Levels';
    protected static ?string $pluralLabel = 'Academic Levels';
    protected static ?int $navigationSort = 0;
    protected static string|UnitEnum|null $navigationGroup = 'Academic';

    public static function form(Schema $schema): Schema
    {
        return AcademicLevelsForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AcademicLevelsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AcademicLevelsTable::configure($table);
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
            'index' => ListAcademicLevels::route('/'),
            'create' => CreateAcademicLevels::route('/create'),
            'view' => ViewAcademicLevels::route('/{record}'),
            'edit' => EditAcademicLevels::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([
            SoftDeletingScope::class
        ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::active()->count();
    }
}
