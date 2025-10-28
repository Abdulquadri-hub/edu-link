<?php

namespace App\Filament\Instructor\Resources\Materials;

use App\Filament\Instructor\Resources\Materials\Pages\CreateMaterial;
use App\Filament\Instructor\Resources\Materials\Pages\EditMaterial;
use App\Filament\Instructor\Resources\Materials\Pages\ListMaterials;
use App\Filament\Instructor\Resources\Materials\Schemas\MaterialForm;
use App\Filament\Instructor\Resources\Materials\Tables\MaterialsTable;
use App\Models\Material;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class MaterialResource extends Resource
{
    protected static ?string $model = Material::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Folder;
    protected static string|UnitEnum|null $navigationGroup = 'Teaching';
    protected static ?string $navigationLabel = 'Course Materials';
    protected static ?int $navigationSort = 4;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('instructor_id', Auth::user()->instructor->id)
            ->with(['course']);
    }

    public static function form(Schema $schema): Schema
    {
        return MaterialForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MaterialsTable::configure($table);
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
            'index' => ListMaterials::route('/'),
            'create' => CreateMaterial::route('/create'),
            'edit' => EditMaterial::route('/{record}/edit'),
        ];
    }
}
