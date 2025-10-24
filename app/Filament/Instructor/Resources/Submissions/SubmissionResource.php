<?php

namespace App\Filament\Instructor\Resources\Submissions;

use UnitEnum;
use BackedEnum;
use App\Models\Submission;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Instructor\Resources\Submissions\Pages\EditSubmission;
use App\Filament\Instructor\Resources\Submissions\Pages\ViewSubmission;
use App\Filament\Instructor\Resources\Submissions\Pages\ListSubmissions;
use App\Filament\Instructor\Resources\Submissions\Pages\CreateSubmission;
use App\Filament\Instructor\Resources\Submissions\Schemas\SubmissionForm;
use App\Filament\Instructor\Resources\Submissions\Tables\SubmissionsTable;
use App\Filament\Instructor\Resources\Submissions\Schemas\SubmissionInfolist;


class SubmissionResource extends Resource
{
    protected static ?string $model = Submission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ClipboardDocumentCheck;
    protected static string|UnitEnum|null $navigationGroup = 'Grading';
    protected static ?int $navigationSort = 1;

    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('assignment', function ($query) {
                $query->where('instructor_id', Auth::user()->instructor->id);
            })
            ->with(['assignment.course', 'student.user', 'grade']);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return SubmissionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SubmissionsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SubmissionInfolist::configure($schema);
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
            'index' => ListSubmissions::route('/'),
            'edit' => EditSubmission::route('/{record}/edit'),
            'view' => ViewSubmission::route('/{record}')
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
        $count = static::getModel()::whereHas('assignment', function ($query) {
            $query->where('instructor_id', Auth::user()->instructor->id);
        })
        ->where('status', 'submitted')
        ->doesntHave('grade')
        ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
