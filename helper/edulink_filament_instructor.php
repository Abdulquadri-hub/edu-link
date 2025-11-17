<?php

/**
 * ==========================================
 * EDULINK FILAMENT INSTRUCTOR PANEL
 * Complete Resources for Instructor Panel
 * ==========================================
 */

// ============================================
// INSTRUCTOR PANEL PROVIDER
// ============================================

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class InstructorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('instructor')
            ->path('instructor')
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->discoverResources(in: app_path('Filament/Instructor/Resources'), for: 'App\\Filament\\Instructor\\Resources')
            ->discoverPages(in: app_path('Filament/Instructor/Pages'), for: 'App\\Filament\\Instructor\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Instructor/Widgets'), for: 'App\\Filament\\Instructor\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->brandName('EduLink Instructor')
            ->favicon(asset('images/favicon.png'));
    }
}

// ============================================
// 1. MY COURSES RESOURCE
// ============================================

namespace App\Filament\Instructor\Resources;

use App\Filament\Instructor\Resources\CourseResource\Pages;
use App\Filament\Instructor\Resources\CourseResource\RelationManagers;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'My Courses';
    protected static ?string $navigationGroup = 'Teaching';
    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('instructors', function ($query) {
                $query->where('instructor_id', auth()->user()->instructor->id);
            })
            ->with(['instructors', 'enrollments', 'classSessions']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Course Information')
                    ->description('View your assigned course details')
                    ->schema([
                        Forms\Components\TextInput::make('course_code')
                            ->disabled(),
                        Forms\Components\TextInput::make('title')
                            ->disabled(),
                        Forms\Components\Select::make('category')
                            ->disabled(),
                        Forms\Components\Select::make('level')
                            ->disabled(),
                        Forms\Components\Placeholder::make('enrolled_count')
                            ->label('Enrolled Students')
                            ->content(fn ($record) => $record->activeEnrollments()->count()),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Course Content')
                    ->schema([
                        Forms\Components\RichEditor::make('description')
                            ->disabled()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->circular(),
                Tables\Columns\TextColumn::make('course_code')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->wrap(),
                Tables\Columns\BadgeColumn::make('category')
                    ->colors([
                        'primary' => 'academic',
                        'success' => 'programming',
                        'info' => 'data-analysis',
                        'warning' => 'tax-audit',
                    ]),
                Tables\Columns\BadgeColumn::make('level'),
                Tables\Columns\TextColumn::make('activeEnrollments_count')
                    ->counts('activeEnrollments')
                    ->label('Students')
                    ->sortable(),
                Tables\Columns\TextColumn::make('classSessions_count')
                    ->counts('classSessions')
                    ->label('Sessions')
                    ->sortable(),
                Tables\Columns\TextColumn::make('assignments_count')
                    ->counts('assignments')
                    ->label('Assignments')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category'),
                Tables\Filters\SelectFilter::make('level'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\StudentsRelationManager::class,
            RelationManagers\ClassSessionsRelationManager::class,
            RelationManagers\AssignmentsRelationManager::class,
            RelationManagers\MaterialsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'view' => Pages\ViewCourse::route('/{record}'),
        ];
    }
}

// ============================================
// 2. CLASS SESSIONS RESOURCE
// ============================================

namespace App\Filament\Instructor\Resources;

use App\Filament\Instructor\Resources\ClassSessionResource\Pages;
use App\Filament\Instructor\Resources\ClassSessionResource\RelationManagers;
use App\Models\ClassSession;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;

class ClassSessionResource extends Resource
{
    protected static ?string $model = ClassSession::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Class Sessions';
    protected static ?string $navigationGroup = 'Teaching';
    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('instructor_id', auth()->user()->instructor->id)
            ->with(['course', 'attendances']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Session Details')
                    ->schema([
                        Forms\Components\Select::make('course_id')
                            ->relationship('course', 'title', function ($query) {
                                $query->whereHas('instructors', function ($q) {
                                    $q->where('instructor_id', auth()->user()->instructor->id);
                                });
                            })
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Introduction to Laravel'),
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Brief description of what will be covered...'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Schedule')
                    ->schema([
                        Forms\Components\DateTimePicker::make('scheduled_at')
                            ->required()
                            ->native(false)
                            ->minDate(now())
                            ->label('Scheduled Date & Time'),
                        Forms\Components\TextInput::make('max_participants')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('Leave blank for unlimited'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Online Meeting')
                    ->schema([
                        Forms\Components\TextInput::make('google_meet_link')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://meet.google.com/xxx-xxxx-xxx')
                            ->helperText('Generate or paste your Google Meet link')
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('generate')
                                    ->icon('heroicon-o-sparkles')
                                    ->action(function (Forms\Set $set) {
                                        $set('google_meet_link', 'https://meet.google.com/' . uniqid());
                                        Notification::make()
                                            ->success()
                                            ->title('Meet link generated')
                                            ->send();
                                    })
                            ),
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->placeholder('Additional notes for this session...'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->wrap(),
                Tables\Columns\TextColumn::make('course.course_code')
                    ->searchable()
                    ->sortable()
                    ->label('Course'),
                Tables\Columns\TextColumn::make('scheduled_at')
                    ->dateTime('M d, Y - H:i')
                    ->sortable()
                    ->description(fn ($record) => $record->scheduled_at->diffForHumans()),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'info' => 'scheduled',
                        'warning' => 'in-progress',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ]),
                Tables\Columns\TextColumn::make('duration_minutes')
                    ->suffix(' min')
                    ->sortable()
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('attendances_count')
                    ->counts('attendances')
                    ->label('Attendance')
                    ->sortable(),
                Tables\Columns\IconColumn::make('google_meet_link')
                    ->boolean()
                    ->label('Meet Link')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'in-progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('course')
                    ->relationship('course', 'title'),
                Tables\Filters\Filter::make('upcoming')
                    ->query(fn (Builder $query) => $query->where('scheduled_at', '>', now()))
                    ->label('Upcoming Only'),
            ])
            ->actions([
                Tables\Actions\Action::make('start')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Start Class Session')
                    ->modalDescription('This will mark the session as in-progress and record the start time.')
                    ->action(function (ClassSession $record) {
                        $record->startSession();
                        Notification::make()
                            ->success()
                            ->title('Class started')
                            ->body('Students can now join.')
                            ->send();
                    })
                    ->visible(fn (ClassSession $record) => $record->status === 'scheduled'),
                
                Tables\Actions\Action::make('end')
                    ->icon('heroicon-o-stop')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('End Class Session')
                    ->modalDescription('This will mark the session as completed and calculate the duration.')
                    ->action(function (ClassSession $record) {
                        $record->endSession();
                        Notification::make()
                            ->success()
                            ->title('Class ended')
                            ->body("Duration: {$record->duration_minutes} minutes")
                            ->send();
                    })
                    ->visible(fn (ClassSession $record) => $record->status === 'in-progress'),
                
                Tables\Actions\Action::make('join')
                    ->icon('heroicon-o-video-camera')
                    ->color('info')
                    ->url(fn (ClassSession $record) => $record->google_meet_link)
                    ->openUrlInNewTab()
                    ->visible(fn (ClassSession $record) => !empty($record->google_meet_link)),
                
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('scheduled_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AttendancesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClassSessions::route('/'),
            'create' => Pages\CreateClassSession::route('/create'),
            'edit' => Pages\EditClassSession::route('/{record}/edit'),
        ];
    }
}

// ============================================
// 3. ASSIGNMENTS RESOURCE
// ============================================

namespace App\Filament\Instructor\Resources;

use App\Filament\Instructor\Resources\AssignmentResource\Pages;
use App\Filament\Instructor\Resources\AssignmentResource\RelationManagers;
use App\Models\Assignment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;

class AssignmentResource extends Resource
{
    protected static ?string $model = Assignment::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Assignments';
    protected static ?string $navigationGroup = 'Teaching';
    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('instructor_id', auth()->user()->instructor->id)
            ->with(['course', 'submissions']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Assignment Details')
                    ->schema([
                        Forms\Components\Select::make('course_id')
                            ->relationship('course', 'title', function ($query) {
                                $query->whereHas('instructors', function ($q) {
                                    $q->where('instructor_id', auth()->user()->instructor->id);
                                });
                            })
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Week 1 Quiz - Laravel Basics'),
                        Forms\Components\Select::make('type')
                            ->options([
                                'quiz' => 'Quiz',
                                'homework' => 'Homework',
                                'project' => 'Project',
                                'exam' => 'Exam',
                                'other' => 'Other',
                            ])
                            ->required()
                            ->native(false)
                            ->default('homework'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                                'closed' => 'Closed',
                            ])
                            ->required()
                            ->native(false)
                            ->default('draft')
                            ->helperText('Only published assignments are visible to students'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Description & Instructions')
                    ->schema([
                        Forms\Components\RichEditor::make('description')
                            ->required()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'link',
                            ])
                            ->placeholder('What is this assignment about?')
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('instructions')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'link',
                            ])
                            ->placeholder('Detailed instructions for students...')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Grading & Deadlines')
                    ->schema([
                        Forms\Components\DateTimePicker::make('assigned_at')
                            ->required()
                            ->native(false)
                            ->default(now())
                            ->label('Assigned Date'),
                        Forms\Components\DateTimePicker::make('due_at')
                            ->required()
                            ->native(false)
                            ->minDate(now())
                            ->label('Due Date'),
                        Forms\Components\TextInput::make('max_score')
                            ->numeric()
                            ->required()
                            ->default(100)
                            ->minValue(1)
                            ->suffix('points'),
                        Forms\Components\Toggle::make('allows_late_submission')
                            ->default(false)
                            ->live()
                            ->label('Allow Late Submissions'),
                        Forms\Components\TextInput::make('late_penalty_percentage')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->label('Late Penalty')
                            ->visible(fn (Forms\Get $get) => $get('allows_late_submission')),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Attachments')
                    ->schema([
                        Forms\Components\FileUpload::make('attachments')
                            ->multiple()
                            ->directory('assignment-attachments')
                            ->maxFiles(5)
                            ->maxSize(10240)
                            ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->columnSpanFull()
                            ->helperText('Upload reference materials, templates, or supporting documents'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->wrap(),
                Tables\Columns\TextColumn::make('course.course_code')
                    ->searchable()
                    ->sortable()
                    ->label('Course'),
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'primary' => 'quiz',
                        'success' => 'homework',
                        'info' => 'project',
                        'danger' => 'exam',
                        'warning' => 'other',
                    ]),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'published',
                        'danger' => 'closed',
                    ]),
                Tables\Columns\TextColumn::make('due_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->description(fn ($record) => $record->due_at->isPast() ? 'Overdue' : $record->due_at->diffForHumans()),
                Tables\Columns\TextColumn::make('max_score')
                    ->suffix(' pts')
                    ->sortable(),
                Tables\Columns\TextColumn::make('submissions_count')
                    ->counts('submissions')
                    ->label('Submissions')
                    ->sortable()
                    ->description(fn ($record) => $record->getGradedCount() . ' graded'),
                Tables\Columns\IconColumn::make('allows_late_submission')
                    ->boolean()
                    ->label('Late OK')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'closed' => 'Closed',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'quiz' => 'Quiz',
                        'homework' => 'Homework',
                        'project' => 'Project',
                        'exam' => 'Exam',
                        'other' => 'Other',
                    ]),
                Tables\Filters\SelectFilter::make('course')
                    ->relationship('course', 'title'),
                Tables\Filters\Filter::make('overdue')
                    ->query(fn (Builder $query) => $query->where('due_at', '<', now())->where('status', 'published'))
                    ->label('Overdue'),
            ])
            ->actions([
                Tables\Actions\Action::make('publish')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Publish Assignment')
                    ->modalDescription('Students will be notified and can start submitting.')
                    ->action(function (Assignment $record) {
                        $record->update(['status' => 'published']);
                        Notification::make()
                            ->success()
                            ->title('Assignment published')
                            ->body('Students have been notified')
                            ->send();
                    })
                    ->visible(fn (Assignment $record) => $record->status === 'draft'),
                
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SubmissionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssignments::route('/'),
            'create' => Pages\CreateAssignment::route('/create'),
            'edit' => Pages\EditAssignment::route('/{record}/edit'),
            'view' => Pages\ViewAssignment::route('/{record}'),
        ];
    }
}

// ============================================
// 4. SUBMISSIONS RESOURCE (For Grading)
// ============================================

namespace App\Filament\Instructor\Resources;

use App\Filament\Instructor\Resources\SubmissionResource\Pages;
use App\Models\Submission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;

class SubmissionResource extends Resource
{
    protected static ?string $model = Submission::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Submissions';
    protected static ?string $navigationGroup = 'Grading';
    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('assignment', function ($query) {
                $query->where('instructor_id', auth()->user()->instructor->id);
            })
            ->with(['assignment.course', 'student.user', 'grade']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Submission Details')
                    ->schema([
                        Forms\Components\Placeholder::make('student_name')
                            ->label('Student')
                            ->content(fn ($record) => $record->student->user->full_name),
                        Forms\Components\Placeholder::make('assignment_title')
                            ->label('Assignment')
                            ->content(fn ($record) => $record->assignment->title),
                        Forms\Components\Placeholder::make('submitted_at')
                            ->label('Submitted')
                            ->content(fn ($record) => $record->submitted_at->format('M d, Y H:i') . ' (' . $record->submitted_at->diffForHumans() . ')'),
                        Forms\Components\Placeholder::make('is_late')
                            ->label('Status')
                            ->content(fn ($record) => $record->is_late ? '⚠️ Late Submission' : '✅ On Time'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Student Work')
                    ->schema([
                        Forms\Components\Placeholder::make('content_display')
                            ->label('Content')
                            ->content(fn ($record) => new \Illuminate\Support\HtmlString($record->content ?? '<em>No text content</em>'))
                            ->columnSpanFull(),
                        Forms\Components\Placeholder::make('attachments_display')
                            ->label('Attachments')
                            ->content(function ($record) {
                                if (empty($record->attachments)) {
                                    return 'No attachments';
                                }
                                $links = collect($record->attachments)->map(function ($file) {
                                    $filename = basename($file);
                                    return "<a href='" . asset('storage/' . $file) . "' target='_blank' class='text-primary-600 underline'>📎 {$filename}</a>";
                                })->join('<br>');
                                return new \Illuminate\Support\HtmlString($links);
                            })
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.student_id')
                    ->searchable()
                    ->sortable()
                    ->label('Student ID'),
                Tables\Columns\TextColumn::make('student.user.full_name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable()
                    ->label('Student Name'),
                Tables\Columns\TextColumn::make('assignment.title')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->label('Assignment'),
                Tables\Columns\TextColumn::make('assignment.course.course_code')
                    ->searchable()
                    ->label('Course'),
                Tables\Columns\TextColumn::make('submitted_at')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->description(fn ($record) => $record->submitted_at->diffForHumans()),
                Tables\Columns\BadgeColumn::make('is_late')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => $state ? 'Late' : 'On Time')
                    ->colors([
                        'danger' => true,
                        'success' => false,
                    ]),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'submitted',
                        'success' => 'graded',
                        'info' => 'returned',
                        'danger' => 'resubmit',
                    ]),
                Tables\Columns\TextColumn::make('grade.percentage')
                    ->label('Grade')
                    ->suffix('%')
                    ->sortable()
                    ->placeholder('Not graded'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'submitted' => 'Submitted',
                        'graded' => 'Graded',
                        'returned' => 'Returned',
                        'resubmit' => 'Resubmit',
                    ]),
                Tables\Filters\TernaryFilter::make('is_late')
                    ->label('Late Submissions'),
                Tables\Filters\SelectFilter::make('assignment')
                    ->relationship('assignment', 'title'),
                Tables\Filters\Filter::make('pending_grading')
                    ->query(fn (Builder $query) => $query->where('status', 'submitted')->doesntHave('grade'))
                    ->label('Pending Grading')
                    ->default(),
            ])
            ->actions([
                Tables\Actions\Action::make('grade')
                    ->icon('heroicon-o-pencil-square')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('score')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->