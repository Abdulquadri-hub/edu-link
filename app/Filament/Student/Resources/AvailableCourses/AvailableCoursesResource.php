<?php

namespace App\Filament\Student\Resources\AvailableCourses;

use UnitEnum;
use BackedEnum;
use App\Models\Course;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\AvailableCourses;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Student\Resources\AvailableCourses\Pages\EditAvailableCourses;
use App\Filament\Student\Resources\AvailableCourses\Pages\ListAvailableCourses;
use App\Filament\Student\Resources\AvailableCourses\Pages\ViewAvailableCourses;
use App\Filament\Student\Resources\AvailableCourses\Pages\CreateAvailableCourses;
use App\Filament\Student\Resources\AvailableCourses\Schemas\AvailableCoursesForm;
use App\Filament\Student\Resources\AvailableCourses\Tables\AvailableCoursesTable;
use App\Filament\Student\Resources\AvailableCourses\Schemas\AvailableCoursesInfolist;

class AvailableCoursesResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::AcademicCap;
    protected static ?string $navigationLabel = 'Available Courses';
    protected static ?string $pluralLabel = 'Available Courses';
    protected static ?int $navigationSort = 0;
    protected static string|UnitEnum|null $navigationGroup = 'Enrollment';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        $student = Auth::user()->student;
        
        return parent::getEloquentQuery()
            // Only show active courses
            ->where('status', 'active')
            // Match student's academic level
            ->where('academic_level_id', $student->academic_level_id)
            // Exclude already enrolled courses
            ->whereDoesntHave('enrollments', function ($query) use ($student) {
                $query->where('student_id', $student->id)
                      ->whereIn('status', ['active', 'pending_payment']);
            })
            ->with(['instructors.user', 'academicLevel']);
    }


    public static function form(Schema $schema): Schema
    {
        return AvailableCoursesForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AvailableCoursesInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AvailableCoursesTable::configure($table);
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
            'index' => ListAvailableCourses::route('/'),
        ];
    }

     public static function getNavigationBadge(): ?string
    {
        $student = Auth::user()->student;
        
        $count = static::getModel()::where('status', 'active')
            ->where('academic_level_id', $student->academic_level_id)
            ->whereDoesntHave('enrollments', function ($query) use ($student) {
                $query->where('student_id', $student->id)
                      ->whereIn('status', ['active', 'pending_payment']);
            })
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
