<?php

namespace App\Filament\Student\Resources\MyEnrollmentRequests;

use UnitEnum;
use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use App\Models\EnrollmentRequest;
use App\Models\MyEnrollmentRequest;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Student\Resources\MyEnrollmentRequests\Pages\EditMyEnrollmentRequest;
use App\Filament\Student\Resources\MyEnrollmentRequests\Pages\ViewMyEnrollmentRequest;
use App\Filament\Student\Resources\MyEnrollmentRequests\Pages\ListMyEnrollmentRequests;
use App\Filament\Student\Resources\MyEnrollmentRequests\Pages\CreateMyEnrollmentRequest;
use App\Filament\Student\Resources\MyEnrollmentRequests\Schemas\MyEnrollmentRequestForm;
use App\Filament\Student\Resources\MyEnrollmentRequests\Tables\MyEnrollmentRequestsTable;
use App\Filament\Student\Resources\MyEnrollmentRequests\Schemas\MyEnrollmentRequestInfolist;

class MyEnrollmentRequestResource extends Resource
{
    protected static ?string $model = EnrollmentRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ClipboardDocumentList;
    
    protected static string|UnitEnum|null $navigationGroup = 'Learning';
    protected static ?string $navigationLabel = 'My Enrollment Requests';
    protected static ?int $navigationSort = 6;

    public static function getEloquentQuery(): Builder
    {
        $student = Auth::user()->student;
        
        return parent::getEloquentQuery()
            ->where('student_id', $student->id)
            ->with(['course', 'processor', 'enrollment']);
    }

    public static function form(Schema $schema): Schema
    {
        return MyEnrollmentRequestForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MyEnrollmentRequestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MyEnrollmentRequestsTable::configure($table);
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
            'index' => ListMyEnrollmentRequests::route('/'),
            'view' => ViewMyEnrollmentRequest::route('/{record}'),
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
        $student = Auth::user()->student;
        
        $count = static::getModel()::where('student_id', $student->id)
            ->whereIn('status', ['pending', 'parent_notified', 'payment_pending'])
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
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
