<?php

namespace App\Filament\Parent\Resources\Children\Tables;

use Filament\Tables\Table;
use Filament\Actions\Action;
use App\Models\AcademicLevel;
use App\Models\Course;
use App\Models\Enrollment;
use App\Services\EnrollmentService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;

class ChildrenTable
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
                    ->weight('bold'),
                
               TextColumn::make('user.full_name')
                    ->label('Name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable()
                    ->description(fn ($record) => $record->user->email),
                
               TextColumn::make('active_enrollments_count')
                    ->counts('activeEnrollments')
                    ->label('Courses')
                    ->badge()
                    ->color('info'),
                
               TextColumn::make('overall_progress')
                    ->label('Progress')
                    ->getStateUsing(fn ($record) => round($record->calculateOverallProgress(), 1) . '%')
                    ->color('success')
                    ->weight('bold'),
                
               TextColumn::make('attendance_rate')
                    ->label('Attendance')
                    ->getStateUsing(fn ($record) => round($record->calculateAttendanceRate(), 1) . '%')
                    ->color(fn ($state) => floatval($state) >= 85 ? 'success' : 'warning'),
                
               TextColumn::make('average_grade')
                    ->label('Avg Grade')
                    ->getStateUsing(function ($record) {
                        $grades = $record->grades()->where('is_published', true)->avg('percentage');
                        return $grades ? round($grades, 1) . '%' : 'N/A';
                    })
                    ->color(fn ($state) => match(true) {
                        $state === 'N/A' => 'gray',
                        floatval($state) >= 80 => 'success',
                        floatval($state) >= 70 => 'info',
                        floatval($state) >= 60 => 'warning',
                        default => 'danger',
                    })
                    ->weight('bold'),
                
               TextColumn::make('enrollment_status')
                    ->badge()
                    ->label('Status')
                    ->colors([
                        'success' => 'active',
                        'info' => 'graduated',
                        'warning' => 'dropped',
                        'danger' => 'suspended',
                    ]),
            ])
            ->filters([
                SelectFilter::make('enrollment_status')
                    ->options([
                        'active' => 'Active',
                        'graduated' => 'Graduated',
                        'dropped' => 'Dropped',
                        'suspended' => 'Suspended',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                
                Action::make('promote')
                    ->label('Promote')
                    ->icon('heroicon-o-chevron-up')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Promote Student')
                    ->modalDescription(fn ($record) => 'Promote ' . $record->user->full_name . ' to the next grade level?')
                    ->modalSubmitActionLabel('Promote')
                    ->action(function ($record, $data) {
                        $actor = Auth::user();
                        $record->promoteToNextLevel($actor, $data['reason'] ?? null);
                    })
                    ->schema([
                        Textarea::make('reason')->label('Reason (optional)'),
                    ])
                    ->visible(fn ($record) => AcademicLevel::where('grade_number', '>', $record->academicLevel?->grade_number ?? 0)->exists()),

                Action::make('enroll_child')
                    ->label('Enroll Child')
                    ->icon('heroicon-o-plus-circle')
                    ->color('primary')
                    ->form([
                        Select::make('course_id')
                            ->label('Course')
                            ->options(function () {
                                $courses = Course::where('status', 'active')
                                    ->get();
                                return $courses->pluck('title', 'id');
                            })
                            ->required(),
                        Select::make('frequency')
                            ->label('Frequency')
                            ->options(['3' => '3x per week', '5' => '5x per week'])
                            ->required(),
                    ])
                    ->action(function ($record, $data) {
                        $student = $record; // Parent's child record is the student
                        $course = Course::find($data['course_id']);
                        if ($course->academic_level_id !== $student->academic_level_id) {
                            throw new \Exception('This course is not available for the student\'s academic level');
                        }

                        if ($student->enrollments()->where('course_id', $course->id)->whereIn('status', ['active', 'pending_payment'])->exists()) {
                            throw new \Exception('Student is already enrolled in this course or pending payment');
                        }

                        $calc = EnrollmentService::calculatePriceForStatic($student, $course, (string)$data['frequency']);

                        $enrollment = Enrollment::create([
                            'student_id' => $student->id,
                            'course_id' => $data['course_id'],
                            'enrolled_at' => now(),
                            'status' => 'pending_payment',
                            'progress_percentage' => 0,
                            'notes' => json_encode($calc['notes']),
                            'frequency' => $data['frequency'],
                            'price' => $calc['price'],
                            'academic_level_id' => $course->academic_level_id,
                        ]);

                        // notify student and parents
                        try {
                            $student->user?->notify(new \App\Notifications\EnrollmentPendingPayment($enrollment, (int)$data['frequency'], $calc['price']));
                            foreach ($student->parents as $parent) {
                                $parent->user?->notify(new \App\Notifications\EnrollmentPendingPayment($enrollment, (int)$data['frequency'], $calc['price']));
                            }
                        } catch (\Exception $e) {
                            // non-blocking
                        }
                    })
                    ->visible(fn ($record) => true),

                Action::make('upload_receipt')
                    ->label('Upload Receipt')
                    ->icon('heroicon-o-upload')
                    ->color('primary')
                    ->form([
                        Select::make('enrollment_id')
                            ->label('Pending Enrollment')
                            ->options(fn($record) => $record->enrollments()->where('status', 'pending_payment')->get()->pluck('course.title', 'id'))
                            ->required(),
                        FileUpload::make('receipt')
                            ->label('Payment Receipt')
                            ->maxSize(10240)
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->required(),
                    ])
                    ->action(function ($record, $data) {
                        $en = \App\Models\Enrollment::find($data['enrollment_id']);
                        if (!$en || $en->student_id !== $record->id) {
                            throw new \Exception('Invalid enrollment selected');
                        }
                        $notes = $en->notes ?? [];
                        $notes['receipt'] = $data['receipt'] ?? null;
                        $en->update(['notes' => $notes]);
                        try {
                            $en->student->user?->notify(new \App\Notifications\EnrollmentPendingPayment($en, (int)$en->frequency, $en->price));
                        } catch (\Exception $e) {
                            // ignore
                        }
                    })
                    ->visible(fn ($record) => $record->enrollments()->where('status', 'pending_payment')->exists()),

                Action::make('viewProgress')
                    ->label('View Progress')
                    ->icon('heroicon-o-chart-bar')
                    ->color('info')
                    ->url(fn ($record) => route('filament.parent.pages.child-progress', ['child' => $record->id])),
            ])
            ->toolbarActions([]);
    }
}
