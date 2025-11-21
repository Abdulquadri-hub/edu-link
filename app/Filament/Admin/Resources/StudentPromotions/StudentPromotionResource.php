<?php

namespace App\Filament\Admin\Resources\StudentPromotions;

use UnitEnum;
use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\StudentPromotion;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Admin\Resources\StudentPromotions\Pages\EditStudentPromotion;
use App\Filament\Admin\Resources\StudentPromotions\Pages\ViewStudentPromotion;
use App\Filament\Admin\Resources\StudentPromotions\Pages\ListStudentPromotions;
use App\Filament\Admin\Resources\StudentPromotions\Pages\CreateStudentPromotion;
use App\Filament\Admin\Resources\StudentPromotions\Schemas\StudentPromotionForm;
use App\Filament\Admin\Resources\StudentPromotions\Tables\StudentPromotionsTable;
use App\Filament\Admin\Resources\StudentPromotions\Schemas\StudentPromotionInfolist;

class StudentPromotionResource extends Resource
{
    protected static ?string $model = StudentPromotion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowTrendingUp;
    
    protected static string|UnitEnum|null $navigationGroup = 'Academic Management';
    protected static ?string $navigationLabel = 'Student Promotions';
    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['student.user', 'fromLevel', 'toLevel', 'promoter', 'approver']);
    }

    public static function form(Schema $schema): Schema
    {
        return StudentPromotionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StudentPromotionsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StudentPromotionInfolist::configure($schema);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStudentPromotions::route('/'),
            'create' => CreateStudentPromotion::route('/create'),
            'view' => ViewStudentPromotion::route('/{record}'),
            'edit' => EditStudentPromotion::route('/{record}/edit'),
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

    public static function canEdit($record): bool
    {
        return $record->status === 'pending';
    }

    public static function canDelete($record): bool
    {
        return $record->status === 'pending';
    }
}