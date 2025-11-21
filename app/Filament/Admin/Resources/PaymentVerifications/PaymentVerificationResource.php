<?php

namespace App\Filament\Admin\Resources\PaymentVerifications;

use App\Filament\Admin\Resources\PaymentVerifications\Pages\CreatePaymentVerification;
use App\Filament\Admin\Resources\PaymentVerifications\Pages\EditPaymentVerification;
use App\Filament\Admin\Resources\PaymentVerifications\Pages\ListPaymentVerifications;
use App\Filament\Admin\Resources\PaymentVerifications\Pages\ViewPaymentVerification;
use App\Filament\Admin\Resources\PaymentVerifications\Schemas\PaymentVerificationForm;
use App\Filament\Admin\Resources\PaymentVerifications\Schemas\PaymentVerificationInfolist;
use App\Filament\Admin\Resources\PaymentVerifications\Tables\PaymentVerificationsTable;
use App\Models\Payment;
use App\Models\PaymentVerification;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PaymentVerificationResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Banknotes;
    
    protected static string|UnitEnum|null $navigationGroup = 'Finance';
    protected static ?string $navigationLabel = 'Payment Verification';
    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
         return parent::getEloquentQuery()
            ->with(['parent.user', 'student.user', 'course', 'verifier', 'subscription']);
    }

    public static function form(Schema $schema): Schema
    {
        return PaymentVerificationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PaymentVerificationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentVerificationsTable::configure($table);
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
            'index' => ListPaymentVerifications::route('/'),
            'view' => ViewPaymentVerification::route('/{record}'),
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
