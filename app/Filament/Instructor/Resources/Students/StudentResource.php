<?php

namespace App\Filament\Instructor\Resources\Students;

use App\Filament\Instructor\Resources\Students\Pages\CreateStudent;
use App\Filament\Instructor\Resources\Students\Pages\EditStudent;
use App\Filament\Instructor\Resources\Students\Pages\ListStudents;
use App\Filament\Instructor\Resources\Students\Schemas\StudentForm;
use App\Filament\Instructor\Resources\Students\Tables\StudentsTable;
use App\Models\Student;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserGroup;
    protected static string|UnitEnum|null $navigationGroup = 'Students';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'My Students';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('enrollments.course.instructors', function ($query) {
                $query->where('instructor_course.instructor_id', Auth::user()->instructor->id);
            })
            ->with(['user', 'enrollments.course', 'attendances', 'submissions']);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    // public static function form(Schema $schema): Schema
    // {
    //     return StudentForm::configure($schema);
    // }

    public static function table(Table $table): Table
    {
        return StudentsTable::configure($table);
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
            'index' => ListStudents::route('/'),
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
        $count = static::getModel()::whereHas('enrollments.course.instructors', function ($query) {
            $query->where('instructor_course.instructor_id', Auth::user()->instructor->id);
        })->count();

        return (string) $count;
    }
}
