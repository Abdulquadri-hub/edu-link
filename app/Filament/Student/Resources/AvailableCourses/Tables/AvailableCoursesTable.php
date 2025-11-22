<?php

namespace App\Filament\Student\Resources\AvailableCourses\Tables;

use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use App\Models\EnrollmentRequest;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Radio;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\RadioGroup;
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
                    ->modalWidth('3xl')
                    ->schema(function ($record) {
                        $student = Auth::user()->student;
                        $checkResult = $student->canRequestEnrollment($record->id);
                        $route = $checkResult['route'] ?? 'parent_payment';
                        
                        // Base form components
                        $components = [
                            TextEntry::make('course_info')
                                ->label('Course Information')
                                ->state(fn () => 
                                    "Course: {$record->title}\n" .
                                    "Code: {$record->course_code}\n" .
                                    "Duration: {$record->duration_weeks} weeks\n" .
                                    "Level: " . ucfirst($record->level)
                                )
                                ->columnSpanFull(),
                            
                            Radio::make('frequency_preference')
                                ->label('Select Frequency')
                                ->options([
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
                        ];
                        
                        // Add parent registration form if needed
                        if ($route === 'parent_registration') {
                            $components[] = Section::make('Parent Information Required')
                                ->description('Since you are under 18 and don\'t have a parent linked, please provide your parent\'s information. We will create an account for them and send instructions.')
                                ->schema([
                                    TextInput::make('parent_first_name')
                                        ->label('Parent First Name')
                                        ->required()
                                        ->maxLength(255),
                                    
                                    TextInput::make('parent_last_name')
                                        ->label('Parent Last Name')
                                        ->required()
                                        ->maxLength(255),
                                    
                                    TextInput::make('parent_email')
                                        ->label('Parent Email')
                                        ->email()
                                        ->required()
                                        ->unique('users', 'email', ignoreRecord: true)
                                        ->helperText('We will send login instructions to this email'),
                                    
                                    TextInput::make('parent_phone')
                                        ->label('Parent Phone')
                                        ->tel()
                                        ->maxLength(20),
                                    
                                    Select::make('relationship')
                                        ->label('Relationship')
                                        ->options([
                                            'father' => 'Father',
                                            'mother' => 'Mother',
                                            'guardian' => 'Legal Guardian',
                                            'grandparent' => 'Grandparent',
                                            'other' => 'Other',
                                        ])
                                        ->required(),
                                ])
                                ->columns(2)
                                ->columnSpanFull();
                        }
                        
                        // Add appropriate next steps message
                        $nextStepsContent = match($route) {
                            'parent_payment' => 'After submitting this request:
                                1. Your parent(s) will be notified via email
                                2. They will need to make payment for the course
                                3. Once payment is verified, you will be enrolled
                                4. You will receive a confirmation and can start attending classes',
                                                            
                            'student_payment' => 'After submitting this request:
                                1. You will be able to upload your payment receipt
                                2. The administration will verify your payment
                                3. Once verified, you will be enrolled in the course
                                4. You will receive a confirmation and can start attending classes',
                            
                            'parent_registration' => 'After submitting this request:
                                1. We will create a parent account with the information provided
                                2. Your parent will receive an email with login instructions
                                3. They will need to log in, update their password, and upload payment receipt
                                4. Once payment is verified, you will be enrolled in the course',
                            
                            default => 'Your request will be reviewed by administration.',
                        };
                        
                        $components[] = TextEntry::make('next_steps')
                            ->label('Next Steps')
                            ->state($nextStepsContent)
                            ->columnSpanFull();
                        
                        return $components;
                    })
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
                            
                            // Handle based on routing
                            $route = $student->getEnrollmentRequestRoute();
                            
                            if ($route === 'parent_registration') {
                                // Create parent account
                                $parentInfo = [
                                    'first_name' => $data['parent_first_name'],
                                    'last_name' => $data['parent_last_name'],
                                    'email' => $data['parent_email'],
                                    'phone' => $data['parent_phone'] ?? null,
                                    'relationship' => $data['relationship'],
                                ];
                                
                                $registration = $request->createParentAccountFromInfo($parentInfo);
                                
                                Notification::make()
                                    ->success()
                                    ->title('Enrollment Request Submitted')
                                    ->body("Request code: {$request->request_code}. A parent account has been created and instructions sent to {$parentInfo['email']}.")
                                    ->duration(10000)
                                    ->send();
                            } else {
                                // Normal flow - notify parent or student
                                $request->notifyParent();
                                
                                $message = match($route) {
                                    'parent_payment' => "Your parent(s) have been notified.",
                                    'student_payment' => "You can now upload your payment receipt.",
                                    default => "Your request has been submitted for review.",
                                };
                                
                                Notification::make()
                                    ->success()
                                    ->title('Enrollment Request Submitted')
                                    ->body("Your request code is {$request->request_code}. {$message}")
                                    ->duration(7000)
                                    ->send();
                            }
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