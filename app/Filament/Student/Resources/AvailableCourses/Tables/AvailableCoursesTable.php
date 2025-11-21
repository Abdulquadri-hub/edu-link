<?php

namespace App\Filament\Student\Resources\AvailableCourses\Tables;

use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use App\Models\EnrollmentRequest;
use Filament\Forms\Components\Radio;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\TextEntry;

class AvailableCoursesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label('Image')
                    ->circular()
                    ->defaultImageUrl(asset('images/default-course.png'))
                    ->imageSize(60),
                
                TextColumn::make('course_code')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->color('primary'),
                
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->wrap()
                    ->description(fn ($record) => $record->category),
                
                TextColumn::make('academicLevel.name')
                    ->label('Grade Level')
                    ->badge()
                    ->color('info')
                    ->placeholder('All Levels'),
                
                TextColumn::make('instructors.user.full_name')
                    ->label('Instructor(s)')
                    ->listWithLineBreaks()
                    ->limitList(2)
                    ->expandableLimitedList(),
                
                TextColumn::make('duration_weeks')
                    ->label('Duration')
                    ->suffix(' weeks')
                    ->alignCenter(),
                
                TextColumn::make('level')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'beginner' => 'success',
                        'intermediate' => 'warning',
                        'advanced' => 'danger',
                    }),
                
                TextColumn::make('price_3x_weekly')
                    ->label('3x Weekly')
                    ->money('USD')
                    ->color('success')
                    ->weight('bold')
                    ->placeholder('Not set'),
                
                TextColumn::make('price_5x_weekly')
                    ->label('5x Weekly')
                    ->money('USD')
                    ->color('success')
                    ->weight('bold')
                    ->placeholder('Not set'),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'academic' => 'Academic',
                        'programming' => 'Programming',
                        'data-analyts' => 'Data Analytics',
                        'tax-audit' => 'Tax Audit',
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
                
                SelectFilter::make('academic_level')
                    ->label('Grade Level')
                    ->relationship('academicLevel', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('request_enrollment')
                    ->label('Request Enrollment')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->modalHeading('Request Course Enrollment')
                    ->modalWidth('2xl')
                    ->schema([
                        TextEntry::make('course_info')
                            ->label('Course Information')
                            ->state(fn ($record) => 
                                "Course: {$record->title}\n" .
                                "Code: {$record->course_code}\n" .
                                "Duration: {$record->duration_weeks} weeks\n" .
                                "Level: " . ucfirst($record->level)
                            )
                            ->columnSpanFull(),
                        
                        Radio::make('frequency_preference')
                            ->label('Select Frequency')
                            ->options(fn ($record) => [
                                '3x_weekly' => '3 times per week - ' . ($record->price_3x_weekly ? '$' . number_format($record->price_3x_weekly, 2) : 'Price not set'),
                                '5x_weekly' => '5 times per week - ' . ($record->price_5x_weekly ? '$' . number_format($record->price_5x_weekly, 2) : 'Price not set'),
                            ])
                            ->required()
                            ->default('3x_weekly')
                            ->descriptions([
                                '3x_weekly' => 'Recommended for beginners',
                                '5x_weekly' => 'Intensive learning schedule',
                            ])
                            ->columnSpanFull(),
                        
                        Textarea::make('student_message')
                            ->label('Why do you want to enroll in this course?')
                            ->rows(4)
                            ->placeholder('Tell us about your interest in this course and your learning goals...')
                            ->helperText('This helps us understand your motivation and goals')
                            ->columnSpanFull(),
                        
                        TextEntry::make('next_steps')
                            ->label('Next Steps')
                            ->state('After submitting this request:
                                1. Your parent(s) will be notified via email
                                2. They will need to make payment for the course
                                3. Once payment is verified, you will be enrolled
                                4. You will receive a confirmation and can start attending classes'
                            )
                            ->columnSpanFull(),
                    ])
                    ->action(function ($record, array $data) {
                        $student = Auth::user()->student;
                        
                        try {
                            // Create enrollment request
                            $request = EnrollmentRequest::create([
                                'student_id' => $student->id,
                                'course_id' => $record->id,
                                'frequency_preference' => $data['frequency_preference'],
                                'student_message' => $data['student_message'] ?? null,
                                'currency' => 'USD',
                            ]);
                            
                            // Notify parent
                            $request->notifyParent();
                            
                            Notification::make()
                                ->success()
                                ->title('Enrollment Request Submitted')
                                ->body("Your request code is {$request->request_code}. Your parent(s) have been notified.")
                                ->duration(7000)
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Request Failed')
                                ->body('There was an error submitting your request. Please try again.')
                                ->send();
                        }
                    }),
                
                ViewAction::make()
                    ->label('View Details'),
            ])
            ->toolbarActions([])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No Available Courses')
            ->emptyStateDescription('There are no courses available for your grade level at this time')
            ->emptyStateIcon('heroicon-o-academic-cap');
    }
}