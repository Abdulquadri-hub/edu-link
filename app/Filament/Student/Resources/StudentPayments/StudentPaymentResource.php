<?php

namespace App\Filament\Student\Resources\StudentPayments;

use UnitEnum;
use BackedEnum;
use App\Models\Payment;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Student\Resources\StudentPayments\Pages\ListStudentPayments;
use App\Filament\Student\Resources\StudentPayments\Pages\CreateStudentPayment;
use App\Filament\Student\Resources\StudentPayments\Pages\ViewStudentPayment;
use App\Filament\Student\Resources\StudentPayments\Schemas\StudentPaymentForm;
use App\Filament\Student\Resources\StudentPayments\Schemas\StudentPaymentInfolist;
use App\Filament\Student\Resources\StudentPayments\Tables\StudentPaymentsTable;

class StudentPaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CreditCard;
    
    protected static string|UnitEnum|null $navigationGroup = 'Finance';
    protected static ?string $navigationLabel = 'My Payments';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Payment';
    protected static ?string $pluralModelLabel = 'My Payments';

    // Only show for adult students
    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        
        if (!$user || !$user->student) {
            return false;
        }
        
        return $user->student->isAdult();
    }

    public static function getEloquentQuery(): Builder
    {
        $student = Auth::user()->student;
        
        return parent::getEloquentQuery()
            ->where('student_id', $student->id)
            ->with(['course', 'verifier', 'subscription']);
    }

    public static function form(Schema $schema): Schema
    {
        return StudentPaymentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StudentPaymentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StudentPaymentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStudentPayments::route('/'),
            'create' => CreateStudentPayment::route('/create'),
            'view' => ViewStudentPayment::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $student = Auth::user()->student;
        
        $count = static::getModel()::where('student_id', $student->id)
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

    // Only accessible by adult students
    public static function canAccess(): bool
    {
        $user = Auth::user();
        
        if (!$user || !$user->student) {
            return false;
        }
        
        return $user->student->isAdult();
    }
}