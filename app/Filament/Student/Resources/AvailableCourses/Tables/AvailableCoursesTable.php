<?php

namespace App\Filament\Student\Resources\AvailableCourses\Tables;

use App\Models\Course;
use App\Models\Enrollment;
use App\Services\EnrollmentService;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Infolists\Components\TextEntry;

class AvailableCoursesTable
{
   public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail')
                    ->circular()
                    ->defaultImageUrl(asset('images/default-course.png')),
                
                TextColumn::make('course_code')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->wrap()
                    ->description(fn ($record) => $record->category),
                
                TextColumn::make('academicLevel.name')
                    ->label('Grade Level')
                    ->badge()
                    ->color(fn ($record) => match($record->academicLevel?->level_type) {
                        'elementary' => 'success',
                        'middle' => 'warning',
                        'high' => 'danger',
                        default => 'gray',
                    }),
                
                TextColumn::make('instructors.user.full_name')
                    ->label('Instructor(s)')
                    ->listWithLineBreaks()
                    ->limitList(2)
                    ->expandableLimitedList(),
                
                TextColumn::make('duration_weeks')
                    ->suffix(' weeks')
                    ->sortable(),
                
                TextColumn::make('level')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'beginner' => 'success',
                        'intermediate' => 'warning',
                        'advanced' => 'danger',
                    }),
                
                TextColumn::make('enrollments_count')
                    ->counts('enrollments')
                    ->label('Students Enrolled')
                    ->badge()
                    ->color('info'),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'academic' => 'Academic',
                        'programming' => 'Programming',
                        'data-analysis' => 'Data Analysis',
                        'tax-audit' => 'Tax & Audit',
                        'business' => 'Business',
                        'counseling' => 'Counseling',
                        'other' => 'Other',
                    ]),
                
                SelectFilter::make('level')
                    ->options([
                        'beginner' => 'Beginner',
                        'intermediate' => 'Intermediate',
                        'advanced' => 'Advanced',
                    ]),
            ])
            ->recordActions([
                Action::make('enroll')
                    ->label('Enroll Now')
                    ->icon('heroicon-o-academic-cap')
                    ->color('success')
                    ->modalHeading(fn ($record) => 'Enroll in ' . $record->title)
                    ->modalDescription('Choose your class frequency and review the pricing')
                    ->schema([
                        Select::make('frequency')
                            ->label('Class Frequency')
                            ->options([
                                '3' => '3x per week',
                                '5' => '5x per week',
                            ])
                            ->required()
                            ->reactive()
                            ->helperText('Choose how many days per week you want to attend classes'),
                        
                        TextEntry::make('pricing_info')
                            ->label('Monthly Fee')
                            ->state(function ($get, $record) {
                                $frequency = $get('frequency');
                                if (!$frequency) return 'Select a frequency to see pricing';
                                
                                $price = $record->calculatePrice((int)$frequency);
                                return "$$price USD per month";
                            }),
                        
                        TextEntry::make('payment_note')
                            ->label('Payment Instructions')
                            ->state('After enrollment, you will receive payment details via email and notification. Please complete payment and upload receipt for admin approval.')
                            ->columnSpanFull(),
                    ])
                    ->action(function (Course $record, array $data) {
                        $student = Auth::user()->student;
                        
                        // Validate eligibility
                        if ($record->academic_level_id !== $student->academic_level_id) {
                            throw new \Exception('This course is not available for your grade level');
                        }

                        if ($student->enrollments()->where('course_id', $record->id)->whereIn('status', ['active', 'pending_payment'])->exists()) {
                            throw new \Exception('You are already enrolled in this course or have a pending payment');
                        }

                        // Calculate price
                        $calc = EnrollmentService::calculatePriceForStatic($student, $record, (string)$data['frequency']);

                        // Create pending enrollment
                        $enrollment = Enrollment::create([
                            'student_id' => $student->id,
                            'course_id' => $record->id,
                            'enrolled_at' => now(),
                            'status' => 'pending_payment',
                            'progress_percentage' => 0,
                            'notes' => json_encode($calc['notes']),
                            'frequency' => $data['frequency'],
                            'price' => $calc['price'],
                            'academic_level_id' => $record->academic_level_id,
                        ]);
                        
                        // Send notification to student
                        // Auth::user()->notify(new EnrollmentRequestNotification(
                        //     $enrollment,
                        //     (int)$data['frequency'],
                        //     $record->calculatePrice((int)$data['frequency'])
                        // ));
                        
                        // // Send notification to parent(s)
                        // foreach ($student->parents as $parent) {
                        //     $parent->user->notify(new EnrollmentRequestNotification(
                        //         $enrollment,
                        //         (int)$data['frequency'],
                        //         $record->calculatePrice((int)$data['frequency']),
                        //         true,
                        //         $student
                        //     ));
                        // }
                        
                        // Notify student and parent(s)
                        try {
                            Auth::user()->notify(new \App\Notifications\EnrollmentPendingPayment(
                                $enrollment,
                                (int)$data['frequency'],
                                $calc['price']
                            ));

                            foreach ($student->parents as $parent) {
                                $parent->user?->notify(new \App\Notifications\EnrollmentPendingPayment(
                                    $enrollment,
                                    (int)$data['frequency'],
                                    $calc['price']
                                ));
                            }
                        } catch (\Exception $e) {
                            // non-blocking
                        }

                        Notification::make()
                            ->success()
                            ->title('Enrollment Request Submitted')
                            ->body('Payment details have been sent to you and your parent(s). Please complete payment to activate enrollment.')
                            ->send();
                    })
                    ->modalWidth('lg')
                    ->slideOver(),
            ])
            ->defaultSort('title', 'asc')
            ->emptyStateHeading('No Available Courses')
            ->emptyStateDescription('There are currently no courses available for your grade level that you haven\'t enrolled in.')
            ->emptyStateIcon('heroicon-o-academic-cap');
    }
}
