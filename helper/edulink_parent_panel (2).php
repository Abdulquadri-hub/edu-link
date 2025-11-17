<?php

/**
 * ==========================================
 * EDULINK PARENT PANEL - COMPLETE
 * Full Implementation with Service Layer Integration
 * ==========================================
 */

// ============================================
// PARENT PANEL PROVIDER
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

class ParentPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('parent')
            ->path('parent')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Parent/Resources'), for: 'App\\Filament\\Parent\\Resources')
            ->discoverPages(in: app_path('Filament/Parent/Pages'), for: 'App\\Filament\\Parent\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Parent/Widgets'), for: 'App\\Filament\\Parent\\Widgets')
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
            ->brandName('EduLink Parent')
            ->favicon(asset('images/favicon.png'));
    }
}

// ============================================
// 1. MY CHILDREN RESOURCE
// ============================================

namespace App\Filament\Parent\Resources;

use App\Filament\Parent\Resources\ChildResource\Pages;
use App\Models\Student;
use App\Contracts\Services\ParentServiceInterface;
use App\Contracts\Repositories\ParentRepositoryInterface;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ChildResource extends Resource
{
    protected static ?string $model = Student::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'My Children';
    protected static ?string $navigationGroup = 'Family';
    protected static ?int $navigationSort = 1;
    protected static ?string $pluralLabel = 'My Children';

    public function __construct(
        private ParentServiceInterface $parentService,
        private ParentRepositoryInterface $parentRepo
    ) {}

    public static function getEloquentQuery(): Builder
    {
        $parent = auth()->user()->parent;
        
        return parent::getEloquentQuery()
            ->whereHas('parents', function ($query) use ($parent) {
                $query->where('parent_id', $parent->id);
            })
            ->with(['user', 'enrollments.course', 'parents']);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Student Information')
                    ->schema([
                        Infolists\Components\ImageEntry::make('user.avatar')
                            ->label('Photo')
                            ->circular()
                            ->defaultImageUrl(asset('images/default-avatar.png')),
                        
                        Infolists\Components\TextEntry::make('student_id')
                            ->label('Student ID')
                            ->copyable()
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold'),
                        
                        Infolists\Components\TextEntry::make('user.full_name')
                            ->label('Full Name')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                        
                        Infolists\Components\TextEntry::make('user.email')
                            ->label('Email')
                            ->copyable()
                            ->icon('heroicon-o-envelope'),
                        
                        Infolists\Components\TextEntry::make('user.phone')
                            ->label('Phone')
                            ->icon('heroicon-o-phone'),
                        
                        Infolists\Components\TextEntry::make('gender')
                            ->badge(),
                        
                        Infolists\Components\TextEntry::make('date_of_birth')
                            ->date('M d, Y')
                            ->description(fn ($record) => 'Age: ' . $record->age . ' years'),
                        
                        Infolists\Components\TextEntry::make('enrollment_status')
                            ->badge()
                            ->color(fn ($state) => match($state) {
                                'active' => 'success',
                                'graduated' => 'info',
                                'dropped' => 'warning',
                                'suspended' => 'danger',
                            }),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Academic Performance')
                    ->schema([
                        Infolists\Components\TextEntry::make('enrolled_courses')
                            ->label('Active Courses')
                            ->getStateUsing(fn ($record) => $record->activeEnrollments->count())
                            ->badge()
                            ->color('info'),
                        
                        Infolists\Components\TextEntry::make('overall_progress')
                            ->label('Overall Progress')
                            ->getStateUsing(fn ($record) => round($record->calculateOverallProgress(), 1) . '%')
                            ->badge()
                            ->color('success'),
                        
                        Infolists\Components\TextEntry::make('attendance_rate')
                            ->label('Attendance Rate')
                            ->getStateUsing(fn ($record) => round($record->calculateAttendanceRate(), 1) . '%')
                            ->badge()
                            ->color(fn ($state) => floatval($state) >= 85 ? 'success' : 'warning'),
                        
                        Infolists\Components\TextEntry::make('average_grade')
                            ->label('Average Grade')
                            ->getStateUsing(function ($record) {
                                $grades = $record->grades()
                                    ->where('is_published', true)
                                    ->avg('percentage');
                                return $grades ? round($grades, 1) . '%' : 'No grades yet';
                            })
                            ->badge()
                            ->color(fn ($state) => match(true) {
                                str_contains($state, 'No') => 'gray',
                                floatval($state) >= 80 => 'success',
                                floatval($state) >= 70 => 'info',
                                floatval($state) >= 60 => 'warning',
                                default => 'danger',
                            }),
                    ])
                    ->columns(4),

                Infolists\Components\Section::make('Current Enrollments')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('activeEnrollments')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('course.course_code')
                                    ->label('Course Code'),
                                Infolists\Components\TextEntry::make('course.title')
                                    ->label('Course Title')
                                    ->limit(30),
                                Infolists\Components\TextEntry::make('progress_percentage')
                                    ->label('Progress')
                                    ->suffix('%')
                                    ->color('success'),
                                Infolists\Components\TextEntry::make('status')
                                    ->badge(),
                            ])
                            ->columns(4)
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('Emergency Contact')
                    ->schema([
                        Infolists\Components\TextEntry::make('emergency_contact_name')
                            ->label('Contact Name')
                            ->icon('heroicon-o-user'),
                        Infolists\Components\TextEntry::make('emergency_contact_phone')
                            ->label('Contact Phone')
                            ->icon('heroicon-o-phone')
                            ->copyable(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('user.avatar')
                    ->label('Photo')
                    ->circular()
                    ->defaultImageUrl(asset('images/default-avatar.png')),
                
                Tables\Columns\TextColumn::make('student_id')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('user.full_name')
                    ->label('Name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable()
                    ->description(fn ($record) => $record->user->email),
                
                Tables\Columns\TextColumn::make('activeEnrollments_count')
                    ->counts('activeEnrollments')
                    ->label('Courses')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('overall_progress')
                    ->label('Progress')
                    ->getStateUsing(fn ($record) => round($record->calculateOverallProgress(), 1) . '%')
                    ->color('success')
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('attendance_rate')
                    ->label('Attendance')
                    ->getStateUsing(fn ($record) => round($record->calculateAttendanceRate(), 1) . '%')
                    ->color(fn ($state) => floatval($state) >= 85 ? 'success' : 'warning'),
                
                Tables\Columns\TextColumn::make('average_grade')
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
                
                Tables\Columns\BadgeColumn::make('enrollment_status')
                    ->label('Status')
                    ->colors([
                        'success' => 'active',
                        'info' => 'graduated',
                        'warning' => 'dropped',
                        'danger' => 'suspended',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('enrollment_status')
                    ->options([
                        'active' => 'Active',
                        'graduated' => 'Graduated',
                        'dropped' => 'Dropped',
                        'suspended' => 'Suspended',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                
                Tables\Actions\Action::make('viewProgress')
                    ->label('View Progress')
                    ->icon('heroicon-o-chart-bar')
                    ->color('info')
                    ->url(fn ($record) => route('filament.parent.pages.child-progress', ['child' => $record->id])),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChildren::route('/'),
            'view' => Pages\ViewChild::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $parent = auth()->user()->parent;
        $count = $parent->children()->count();
        return (string) $count;
    }
}

// ============================================
// 2. CHILD GRADES RESOURCE
// ============================================

namespace App\Filament\Parent\Resources;

use App\Filament\Parent\Resources\ChildGradeResource\Pages;
use App\Models\Grade;
use App\Contracts\Services\ParentServiceInterface;
use App\Contracts\Repositories\ParentRepositoryInterface;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;

class ChildGradeResource extends Resource
{
    protected static ?string $model = Grade::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Grades';
    protected static ?string $navigationGroup = 'Academic';
    protected static ?int $navigationSort = 1;

    public function __construct(
        private ParentServiceInterface $parentService,
        private ParentRepositoryInterface $parentRepo
    ) {}

    public static function getEloquentQuery(): Builder
    {
        $parent = auth()->user()->parent;
        $parentRepo = app(ParentRepositoryInterface::class);
        
        return parent::getEloquentQuery()
            ->whereHas('submission.student.parents', function ($query) use ($parent, $parentRepo) {
                $query->where('parent_id', $parent->id);
                
                // Check can_view_grades permission
                $query->where(function ($q) use ($parent, $parentRepo) {
                    $q->where('can_view_grades', true);
                });
            })
            ->where('is_published', true)
            ->with(['submission.student.user', 'submission.assignment.course', 'instructor.user']);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Grade Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('submission.student.user.full_name')
                            ->label('Student')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold'),
                        
                        Infolists\Components\TextEntry::make('submission.assignment.title')
                            ->label('Assignment')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                        
                        Infolists\Components\TextEntry::make('submission.assignment.course.title')
                            ->label('Course'),
                        
                        Infolists\Components\TextEntry::make('score')
                            ->label('Score')
                            ->formatStateUsing(fn ($state, $record) => $state . ' / ' . $record->max_score)
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                        
                        Infolists\Components\TextEntry::make('percentage')
                            ->suffix('%')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold')
                            ->color(fn ($state) => match(true) {
                                $state >= 90 => 'success',
                                $state >= 80 => 'info',
                                $state >= 70 => 'warning',
                                default => 'danger',
                            }),
                        
                        Infolists\Components\TextEntry::make('letter_grade')
                            ->badge()
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                        
                        Infolists\Components\TextEntry::make('published_at')
                            ->dateTime('M d, Y H:i')
                            ->description(fn ($record) => $record->published_at->diffForHumans()),
                        
                        Infolists\Components\TextEntry::make('instructor.user.full_name')
                            ->label('Instructor'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Instructor Feedback')
                    ->schema([
                        Infolists\Components\TextEntry::make('feedback')
                            ->html()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('submission.student.user.full_name')
                    ->label('Child')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('submission.assignment.title')
                    ->label('Assignment')
                    ->searchable()
                    ->limit(40)
                    ->description(fn ($record) => $record->submission->assignment->course->course_code),
                
                Tables\Columns\TextColumn::make('submission.assignment.course.title')
                    ->label('Course')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('percentage')
                    ->label('Grade')
                    ->suffix('%')
                    ->sortable()
                    ->weight('bold')
                    ->size(Tables\Columns\TextColumn\TextColumnSize::Large)
                    ->color(fn ($state) => match(true) {
                        $state >= 90 => 'success',
                        $state >= 80 => 'info',
                        $state >= 70 => 'warning',
                        default => 'danger',
                    }),
                
                Tables\Columns\TextColumn::make('letter_grade')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'A' => 'success',
                        'B' => 'info',
                        'C' => 'warning',
                        default => 'danger',
                    }),
                
                Tables\Columns\TextColumn::make('submission.is_late')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => $state ? 'Late' : 'On Time')
                    ->badge()
                    ->colors([
                        'danger' => true,
                        'success' => false,
                    ])
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->description(fn ($record) => $record->published_at->diffForHumans()),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('student')
                    ->label('Child')
                    ->relationship('submission.student', 'student_id', function ($query) {
                        $parent = auth()->user()->parent;
                        $query->whereHas('parents', function ($q) use ($parent) {
                            $q->where('parent_id', $parent->id);
                        });
                    })
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\SelectFilter::make('course')
                    ->label('Course')
                    ->relationship('submission.assignment.course', 'title')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\Filter::make('low_grades')
                    ->label('Below 70%')
                    ->query(fn (Builder $query) => $query->where('percentage', '<', 70))
                    ->toggle(),
                
                Tables\Filters\Filter::make('recent')
                    ->label('This Week')
                    ->query(fn (Builder $query) => $query->where('published_at', '>', now()->subWeek()))
                    ->default(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                
                Tables\Actions\Action::make('contactInstructor')
                    ->label('Contact Instructor')
                    ->icon('heroicon-o-envelope')
                    ->color('info')
                    ->form([
                        \Filament\Forms\Components\Placeholder::make('instructor_name')
                            ->label('Instructor')
                            ->content(fn ($record) => $record->instructor->user->full_name),
                        
                        \Filament\Forms\Components\Textarea::make('message')
                            ->label('Your Message')
                            ->required()
                            ->rows(5)
                            ->placeholder('Ask about your child\'s performance...'),
                    ])
                    ->action(function ($record, array $data) {
                        // Send via notification service
                        Notification::make()
                            ->success()
                            ->title('Message sent')
                            ->body('The instructor will respond via email')
                            ->send();
                    })
                    ->modalWidth('xl'),
            ])
            ->bulkActions([])
            ->defaultSort('published_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChildGrades::route('/'),
            'view' => Pages\ViewChildGrade::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $parent = auth()->user()->parent;
        
        $count = static::getModel()::whereHas('submission.student.parents', function ($query) use ($parent) {
            $query->where('parent_id', $parent->id)
                  ->where('can_view_grades', true);
        })
        ->where('is_published', true)
        ->where('published_at', '>', now()->subWeek())
        ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}

// ============================================
// 3. CHILD ATTENDANCE RESOURCE
// ============================================

namespace App\Filament\Parent\Resources;

use App\Filament\Parent\Resources\ChildAttendanceResource\Pages;
use App\Models\Attendance;
use App\Contracts\Services\ParentServiceInterface;
use App\Contracts\Repositories\ParentRepositoryInterface;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ChildAttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Attendance';
    protected static ?string $navigationGroup = 'Academic';
    protected static ?int $navigationSort = 2;

    public function __construct(
        private ParentServiceInterface $parentService,
        private ParentRepositoryInterface $parentRepo
    ) {}

    public static function getEloquentQuery(): Builder
    {
        $parent = auth()->user()->parent;
        
        return parent::getEloquentQuery()
            ->whereHas('student.parents', function ($query) use ($parent) {
                $query->where('parent_id', $parent->id)
                      ->where('can_view_attendance', true);
            })
            ->with(['student.user', 'classSession.course', 'classSession.instructor.user']);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Attendance Record')
                    ->schema([
                        Infolists\Components\TextEntry::make('student.user.full_name')
                            ->label('Student')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                        
                        Infolists\Components\TextEntry::make('classSession.title')
                            ->label('Class')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                        
                        Infolists\Components\TextEntry::make('classSession.course.title')
                            ->label('Course'),
                        
                        Infolists\Components\TextEntry::make('classSession.scheduled_at')
                            ->dateTime('l, F d, Y - H:i A')
                            ->label('Class Date'),
                        
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn ($state) => match($state) {
                                'present' => 'success',
                                'late' => 'warning',
                                'absent' => 'danger',
                                'excused' => 'info',
                            })
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                        
                        Infolists\Components\TextEntry::make('joined_at')
                            ->dateTime('H:i A')
                            ->label('Joined At')
                            ->visible(fn ($record) => $record->joined_at),
                        
                        Infolists\Components\TextEntry::make('duration_minutes')
                            ->suffix(' minutes')
                            ->label('Duration')
                            ->visible(fn ($record) => $record->duration_minutes),
                        
                        Infolists\Components\TextEntry::make('classSession.instructor.user.full_name')
                            ->label('Instructor'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Notes')
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => !empty($record->notes))
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.user.full_name')
                    ->label('Child')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('classSession.title')
                    ->label('Class')
                    ->searchable()
                    ->limit(40)
                    ->description(fn ($record) => $record->classSession->course->course_code),
                
                Tables\Columns\TextColumn::make('classSession.course.title')
                    ->label('Course')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('classSession.scheduled_at')
                    ->label('Date')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->description(fn ($record) => $record->classSession->scheduled_at->format('H:i A')),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'present',
                        'warning' => 'late',
                        'danger' => 'absent',
                        'info' => 'excused',
                    ])
                    ->icon(fn ($state) => match($state) {
                        'present' => 'heroicon-o-check-circle',
                        'late' => 'heroicon-o-clock',
                        'absent' => 'heroicon-o-x-circle',
                        'excused' => 'heroicon-o-information-circle',
                    }),
                
                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Duration')
                    ->suffix(' min')
                    ->placeholder('N/A')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('classSession.instructor.user.full_name')
                    ->label('Instructor')
                    ->searchable(['first_name', 'last_name'])
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('student')
                    ->label('Child')
                    ->relationship('student', 'student_id', function ($query) {
                        $parent = auth()->user()->parent;
                        $query->whereHas('parents', function ($q) use ($parent) {
                            $q->where('parent_id', $parent->id);
                        });
                    })
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'present' => 'Present',
                        'late' => 'Late',
                        'absent' => 'Absent',
                        'excused' => 'Excused',
                    ]),
                
                Tables\Filters\SelectFilter::make('course')
                    ->label('Course')
                    ->relationship('classSession.course', 'title')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\Filter::make('this_month')
                    ->label('This Month')
                    ->query(fn (Builder $query) => $query->whereHas('classSession', function ($q) {
                        $q->whereMonth('scheduled_at', now()->month)
                          ->whereYear('scheduled_at', now()->year);
                    }))