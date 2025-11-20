<?php

namespace App\Filament\Instructor\Resources\ParentUploadedAssignments;

use UnitEnum;
use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\ParentAssignment;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use App\Models\ParentUploadedAssignment;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Instructor\Resources\ParentUploadedAssignments\Pages\EditParentUploadedAssignment;
use App\Filament\Instructor\Resources\ParentUploadedAssignments\Pages\ViewParentUploadedAssignment;
use App\Filament\Instructor\Resources\ParentUploadedAssignments\Pages\ListParentUploadedAssignments;
use App\Filament\Instructor\Resources\ParentUploadedAssignments\Pages\CreateParentUploadedAssignment;
use App\Filament\Instructor\Resources\ParentUploadedAssignments\Schemas\ParentUploadedAssignmentForm;
use App\Filament\Instructor\Resources\ParentUploadedAssignments\Tables\ParentUploadedAssignmentsTable;
use App\Filament\Instructor\Resources\ParentUploadedAssignments\Schemas\ParentUploadedAssignmentInfolist;

class ParentUploadedAssignmentResource extends Resource
{
    protected static ?string $model = ParentAssignment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::InboxArrowDown;
    
    protected static string|UnitEnum|null $navigationGroup = 'Teaching';
    protected static ?string $navigationLabel = 'Parent Uploads';
    protected static ?int $navigationSort = 5;
    protected static ?string $modelLabel = 'Parent Upload';
    protected static ?string $pluralModelLabel = 'Parent Uploads';

    public static function getEloquentQuery(): Builder
    {
        $instructor = Auth::user()->instructor;
        $instructorId = $instructor->id;
        
        return parent::getEloquentQuery()
            ->where(function (Builder $query) use ($instructorId) {
                // Show uploads related to assignments in the instructor's courses
                $query->whereHas('assignment.course.instructors', function ($q) use ($instructorId) {
                    $q->where('instructor_course.instructor_id', $instructorId);
                    $q->where('instructors.deleted_at', null);
                })
                // OR show uploads related to courses taught by the instructor (for 'teach' status uploads)
                ->orWhereHas('course.instructors', function ($q) use ($instructorId) {
                    $q->where('instructor_course.instructor_id', $instructorId);
                    $q->where('instructors.deleted_at', null);
                });
            })
            ->whereIn('status', ['submitted', 'teach', 'graded'])
            ->with(['student.user', 'parent.user', 'assignment.course', 'course', 'submission.grade']);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }


    public static function form(Schema $schema): Schema
    {
        return ParentUploadedAssignmentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ParentUploadedAssignmentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ParentUploadedAssignmentsTable::configure($table);
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
            'index' => ListParentUploadedAssignments::route('/'),
            'create' => CreateParentUploadedAssignment::route('/create'),
            'view' => ViewParentUploadedAssignment::route('/{record}'),
            'edit' => EditParentUploadedAssignment::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $instructor = Auth::user()->instructor;
        
        $count = static::getModel()::whereHas('assignment.course.instructors', function ($query) use ($instructor) {
            $query->where('instructor_course.instructor_id', $instructor->id);
        })
        ->where('status', 'submitted')
        ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }
}
