<?php

namespace App\Filament\Parent\Resources\Payments;

use UnitEnum;
use BackedEnum;
use App\Models\Payment;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Parent\Resources\Payments\Pages\EditPayment;
use App\Filament\Parent\Resources\Payments\Pages\ViewPayment;
use App\Filament\Parent\Resources\Payments\Pages\ListPayments;
use App\Filament\Parent\Resources\Payments\Pages\CreatePayment;
use App\Filament\Parent\Resources\Payments\Schemas\PaymentForm;
use App\Filament\Parent\Resources\Payments\Tables\PaymentsTable;
use App\Filament\Parent\Resources\Payments\Schemas\PaymentInfolist;

class MyPaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CreditCard;

    protected static string|UnitEnum|null $navigationGroup = 'Finance';
    protected static ?string $navigationLabel = 'Payments';
    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        $parent = Auth::user()->parent;
        
        return parent::getEloquentQuery()
            ->where('parent_id', $parent->id)
            ->with(['student.user', 'course', 'verifier', 'subscription']);
    }

    public static function form(Schema $schema): Schema
    {
        return PaymentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PaymentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentsTable::configure($table);
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
            'index' => ListPayments::route('/'),
            'create' => CreatePayment::route('/create'),
            'view' => ViewPayment::route('/{record}'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
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
