<?php

namespace App\Filament\Instructor\Resources\Students\Tables;

use App\Models\Student;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Query\Builder;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\ForceDeleteBulkAction;

class StudentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('user.avatar')
                    ->label('Photo')
                    ->circular()
                    ->defaultImageUrl(asset('images/default-avatar.png')),
                
                TextColumn::make('student_id')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->label('Student ID'),
                
                TextColumn::make('user.full_name')
                    ->label('Name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable()
                    ->description(fn ($record) => $record->user->email),
                
                TextColumn::make('user.phone')
                    ->label('Phone')
                    ->searchable()
                    ->toggleable()
                    ->icon('heroicon-o-phone'),
                
                TextColumn::make('enrolled_courses')
                    ->label('My Courses')
                    ->getStateUsing(function (Student $record) {
                        return $record->enrollments()
                            ->whereHas('course.instructors', function ($query) {
                                $query->where('instructor_course.instructor_id', Auth::user()->instructor->id);
                            })
                            ->where('status', 'active')
                            ->count();
                    })
                    ->badge()
                    ->color('info')
                    ->sortable(),
                
                TextColumn::make('average_grade')
                    ->label('Avg Grade')
                    ->getStateUsing(function (Student $record) {
                        $grades = $record->grades()
                            ->whereHas('submission.assignment', function ($query) {
                                $query->where('instructor_id', Auth::user()->instructor->id);
                            })
                            ->where('is_published', true)
                            ->avg('percentage');
                        
                        return $grades ? round($grades, 1) . '%' : 'N/A';
                    })
                    ->color(fn ($state) => match(true) {
                        $state === 'N/A' => 'gray',
                        floatval($state) >= 90 => 'success',
                        floatval($state) >= 80 => 'info',
                        floatval($state) >= 70 => 'warning',
                        default => 'danger',
                    })
                    ->weight('bold')
                    ->sortable(),
                
                TextColumn::make('attendance_rate')
                    ->label('Attendance')
                    ->getStateUsing(function (Student $record) {
                        $total = $record->attendances()
                            ->whereHas('classSession', function ($query) {
                                $query->where('instructor_id', Auth::user()->instructor->id);
                            })
                            ->count();
                        
                        if ($total === 0) return 'N/A';
                        
                        $present = $record->attendances()
                            ->whereHas('classSession', function ($query) {
                                $query->where('instructor_id', Auth::user()->instructor->id);
                            })
                            ->where('status', 'present')
                            ->count();
                        
                        return round(($present / $total) * 100, 1) . '%';
                    })
                    ->color(fn ($state) => match(true) {
                        $state === 'N/A' => 'gray',
                        floatval($state) >= 85 => 'success',
                        floatval($state) >= 75 => 'warning',
                        default => 'danger',
                    })
                    ->sortable(),
                
                TextColumn::make('submissions_count')
                    ->label('Submissions')
                    ->getStateUsing(function (Student $record) {
                        return $record->submissions()
                            ->whereHas('assignment', function ($query) {
                                $query->where('instructor_id', Auth::user()->instructor->id);
                            })
                            ->count();
                    })
                    ->badge()
                    ->color('primary')
                    ->toggleable(),
                
                TextColumn::make('enrollment_status')
                    ->badge()
                    ->label('Status')
                    ->colors([
                        'success' => 'active',
                        'info' => 'graduated',
                        'warning' => 'dropped',
                        'danger' => 'suspended',
                    ])
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('course')
                    ->label('Filter by Course')
                    ->relationship('enrollments.course', 'title', function ($query) {
                        $query->whereHas('instructors', function ($q) {
                            $q->where('instructor_course.instructor_id', Auth::user()->instructor->id);
                        });
                    })
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('gender')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                        'other' => 'Other',
                    ]),
                
                // Filter::make('low_performance')
                //     ->label('Low Performance (<60%)')
                //     ->query(function (Builder $query) {
                //         $instructorId = Auth::user()->instructor->id;
                //         $query->whereHas('grades', function ($q) use ($instructorId) {
                //             $q->where('is_published', true)
                //                 ->whereHas('submission.assignment', function ($sq) use ($instructorId) {
                //                     $sq->where('instructor_id', $instructorId);
                //                 });
                //         })
                //         ->whereRaw('(SELECT AVG(percentage) FROM grades WHERE grades.submission_id IN (SELECT id FROM submissions WHERE student_id = students.id)) < 60');
                //     })
                //     ->toggle(),
                
                // Filter::make('poor_attendance')
                //     ->label('Poor Attendance (<75%)')
                //     ->toggle(),
            ])
            ->recordActions([
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
