<?php

namespace App\Filament\Student\Resources\AvailableCourses;

use UnitEnum;
use BackedEnum;
use App\Models\Course;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Student\Resources\AvailableCourses\Pages\ListAvailableCourses;
use App\Filament\Student\Resources\AvailableCourses\Pages\ViewAvailableCourse;
use App\Filament\Student\Resources\AvailableCourses\Schemas\AvailableCourseInfolist;
use App\Filament\Student\Resources\AvailableCourses\Tables\AvailableCoursesTable;

class AvailableCourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::AcademicCap;
    
    protected static string|UnitEnum|null $navigationGroup = 'Learning';
    protected static ?string $navigationLabel = 'Available Courses';
    protected static ?int $navigationSort = 5;
    protected static ?string $modelLabel = 'Available Course';
    protected static ?string $pluralModelLabel = 'Available Courses';

    public static function getEloquentQuery(): Builder
    {
        $student = Auth::user()->student;
        
        return parent::getEloquentQuery()
            ->where('status', 'active')
            // Filter by student's academic level if set
            ->when($student->academic_level_id, function ($query) use ($student) {
                $query->where('academic_level_id', $student->academic_level_id);
            })
            // Exclude courses already enrolled in
            ->whereDoesntHave('enrollments', function ($query) use ($student) {
                $query->where('student_id', $student->id)
                      ->where('status', 'active');
            })
            // Exclude courses with pending enrollment requests
            ->whereDoesntHave('enrollmentRequests', function ($query) use ($student) {
                $query->where('student_id', $student->id)
                      ->whereIn('status', ['pending', 'parent_notified', 'payment_pending']);
            })
            ->with(['instructors.user', 'academicLevel']);
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

    public static function infolist(Schema $schema): Schema
    {
        return AvailableCourseInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AvailableCoursesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAvailableCourses::route('/'),
            'view' => ViewAvailableCourse::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $student = Auth::user()->student;
        
        $count = static::getModel()::where('status', 'active')
            ->when($student->academic_level_id, function ($query) use ($student) {
                $query->where('academic_level_id', $student->academic_level_id);
            })
            ->whereDoesntHave('enrollments', function ($query) use ($student) {
                $query->where('student_id', $student->id)->where('status', 'active');
            })
            ->whereDoesntHave('enrollmentRequests', function ($query) use ($student) {
                $query->where('student_id', $student->id)
                      ->whereIn('status', ['pending', 'parent_notified', 'payment_pending']);
            })
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}