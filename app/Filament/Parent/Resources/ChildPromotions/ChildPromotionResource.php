<?php

namespace App\Filament\Parent\Resources\ChildPromotions;

use UnitEnum;
use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\ChildPromotion;
use App\Models\StudentPromotion;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Parent\Resources\ChildPromotions\Pages\EditChildPromotion;
use App\Filament\Parent\Resources\ChildPromotions\Pages\ViewChildPromotion;
use App\Filament\Parent\Resources\ChildPromotions\Pages\ListChildPromotions;
use App\Filament\Parent\Resources\ChildPromotions\Pages\CreateChildPromotion;
use App\Filament\Parent\Resources\ChildPromotions\Schemas\ChildPromotionForm;
use App\Filament\Parent\Resources\ChildPromotions\Tables\ChildPromotionsTable;
use App\Filament\Parent\Resources\ChildPromotions\Schemas\ChildPromotionInfolist;

class ChildPromotionResource extends Resource
{
    protected static ?string $model = StudentPromotion::class;

     protected static string|BackedEnum|null $navigationIcon = Heroicon::AcademicCap;
    
    protected static string|UnitEnum|null $navigationGroup = 'Academic';
    protected static ?string $navigationLabel = 'Promotions';
    protected static ?int $navigationSort = 5;
    protected static ?string $pluralModelLabel = 'Child Promotions';

    public static function getEloquentQuery(): Builder
    {
        $parent = Auth::user()->parent;

        return parent::getEloquentQuery()
            ->whereHas('student.parents', function ($query) use ($parent) {
                $query->where('student_parent.parent_id', $parent->id);
            })
            ->with(['student.user', 'fromLevel', 'toLevel', 'promoter', 'approver']);
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
    public static function form(Schema $schema): Schema
    {
        return ChildPromotionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ChildPromotionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChildPromotionsTable::configure($table);
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
            'index' => ListChildPromotions::route('/'),
            'view' => ViewChildPromotion::route('/{record}'),
        ];
    }
}
