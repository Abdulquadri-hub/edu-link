<?php

namespace App\Filament\Instructor\Resources\Courses;

use UnitEnum;
use BackedEnum;
use App\Models\Course;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Instructor\Resources\Courses\Pages\EditCourse;
use App\Filament\Instructor\Resources\Courses\Pages\ViewCourse;
use App\Filament\Instructor\Resources\Courses\Pages\ListCourses;
use App\Filament\Instructor\Resources\Courses\Pages\CreateCourse;
use App\Filament\Instructor\Resources\Courses\Schemas\CourseForm;
use App\Filament\Instructor\Resources\Courses\Tables\CoursesTable;


class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::BookOpen;

    protected static ?string $navigationLabel = "My Courses";
    protected static string|UnitEnum|null $navigationGroup = 'Teaching';
    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('instructors', function ($query) {
               $query->where('instructor_course.instructor_id', Auth::user()->instructor->id);
           })->with(['instructors', 'enrollments', 'classSessions']);
    }

    public static function form(Schema $schema): Schema
    {
        return CourseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CoursesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\StudentsRelationManager::class,
            // RelationManagers\ClassSessionsRelationManager::class,
            // RelationManagers\AssignmentsRelationManager::class,
            // RelationManagers\MaterialsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCourses::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
