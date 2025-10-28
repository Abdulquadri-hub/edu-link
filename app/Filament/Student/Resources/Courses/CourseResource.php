<?php

namespace App\Filament\Student\Resources\Courses;

use App\Filament\Student\Resources\Courses\Pages\CreateCourse;
use App\Filament\Student\Resources\Courses\Pages\EditCourse;
use App\Filament\Student\Resources\Courses\Pages\ListCourses;
use App\Filament\Student\Resources\Courses\Pages\ViewCourse;
use App\Filament\Student\Resources\Courses\Schemas\CourseForm;
use App\Filament\Student\Resources\Courses\Schemas\CourseInfolist;
use App\Filament\Student\Resources\Courses\Tables\CoursesTable;
use App\Models\Course;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::BookOpen;
    protected static ?string $navigationLabel = 'My Courses';
    protected static ?int $navigationSort = 1;
    protected static string|UnitEnum|null $navigationGroup = 'Learning';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        $student = Auth::user()->student;

        return parent::getEloquentQuery()
            ->whereHas('enrollments',  function ($query) use($student) {
                $query->where('student_id', $student->id)
                     ->where('status', 'active');
            })
            ->with(['instructors', 'enrollments' => function ($query) use ($student) {
                $query->where('student_id', $student->id);
            }]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CourseInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CoursesTable::configure($table);
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
            'index' => ListCourses::route('/'),
            'view' => ViewCourse::route('/{record}')
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
        return (string) $student->enrollments()->where('status', 'active')->count();
    }
}
