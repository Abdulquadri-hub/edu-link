<?php

/**
 * ==========================================
 * EDULINK FILAMENT ADMIN PANEL
 * Complete Resources for Admin Panel
 * ==========================================
 */

// ============================================
// ADMIN PANEL PROVIDER
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

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
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
            ->brandName('EduLink Admin')
            ->favicon(asset('images/favicon.png'));
    }
}

// ============================================
// 1. USER RESOURCE
// ============================================

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'User Management';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('username')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('avatar')
                            ->image()
                            ->directory('avatars')
                            ->imageEditor(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Account Settings')
                    ->schema([
                        Forms\Components\Select::make('user_type')
                            ->options([
                                'admin' => 'Admin',
                                'instructor' => 'Instructor',
                                'student' => 'Student',
                                'parent' => 'Parent',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'suspended' => 'Suspended',
                            ])
                            ->required()
                            ->native(false)
                            ->default('active'),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->maxLength(255),
                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->native(false),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->circular(),
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('username')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('user_type')
                    ->colors([
                        'primary' => 'admin',
                        'success' => 'instructor',
                        'info' => 'student',
                        'warning' => 'parent',
                    ]),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'inactive',
                        'danger' => 'suspended',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_type')
                    ->options([
                        'admin' => 'Admin',
                        'instructor' => 'Instructor',
                        'student' => 'Student',
                        'parent' => 'Parent',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'suspended' => 'Suspended',
                    ]),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes()
            ->with(['student', 'parent', 'instructor']);
    }
}

// ============================================
// 2. STUDENT RESOURCE
// ============================================

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\StudentResource\Pages;
use App\Filament\Admin\Resources\StudentResource\RelationManagers;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'User Management';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Student Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'email')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('first_name')->required(),
                                Forms\Components\TextInput::make('last_name')->required(),
                                Forms\Components\TextInput::make('email')->email()->required(),
                                Forms\Components\TextInput::make('username')->required(),
                                Forms\Components\TextInput::make('password')->password()->required(),
                            ]),
                        Forms\Components\TextInput::make('student_id')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(fn () => 'STU' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT))
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('date_of_birth')
                            ->required()
                            ->native(false)
                            ->maxDate(now()),
                        Forms\Components\Select::make('gender')
                            ->options([
                                'male' => 'Male',
                                'female' => 'Female',
                                'other' => 'Other',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\DatePicker::make('enrollment_date')
                            ->required()
                            ->native(false)
                            ->default(now()),
                        Forms\Components\Select::make('enrollment_status')
                            ->options([
                                'active' => 'Active',
                                'graduated' => 'Graduated',
                                'dropped' => 'Dropped',
                                'suspended' => 'Suspended',
                            ])
                            ->required()
                            ->native(false)
                            ->default('active'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Contact Information')
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->rows(2)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('city')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('state')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('country')
                            ->default('Nigeria')
                            ->maxLength(255),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Emergency Contact')
                    ->schema([
                        Forms\Components\TextInput::make('emergency_contact_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('emergency_contact_phone')
                            ->tel()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Additional Notes')
                    ->schema([
                        Forms\Components\RichEditor::make('notes')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student_id')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('user.first_name')
                    ->label('First Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.last_name')
                    ->label('Last Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('gender')
                    ->badge()
                    ->colors([
                        'primary' => 'male',
                        'success' => 'female',
                        'warning' => 'other',
                    ]),
                Tables\Columns\BadgeColumn::make('enrollment_status')
                    ->colors([
                        'success' => 'active',
                        'info' => 'graduated',
                        'warning' => 'dropped',
                        'danger' => 'suspended',
                    ]),
                Tables\Columns\TextColumn::make('enrollments_count')
                    ->counts('enrollments')
                    ->label('Courses')
                    ->sortable(),
                Tables\Columns\TextColumn::make('parents_count')
                    ->counts('parents')
                    ->label('Parents')
                    ->sortable(),
                Tables\Columns\TextColumn::make('enrollment_date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('enrollment_status')
                    ->options([
                        'active' => 'Active',
                        'graduated' => 'Graduated',
                        'dropped' => 'Dropped',
                        'suspended' => 'Suspended',
                    ]),
                Tables\Filters\SelectFilter::make('gender'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\EnrollmentsRelationManager::class,
            RelationManagers\ParentsRelationManager::class,
            RelationManagers\AttendancesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
            'view' => Pages\ViewStudent::route('/{record}'),
        ];
    }
}

// ============================================
// 3. INSTRUCTOR RESOURCE
// ============================================

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\InstructorResource\Pages;
use App\Filament\Admin\Resources\InstructorResource\RelationManagers;
use App\Models\Instructor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InstructorResource extends Resource
{
    protected static ?string $model = Instructor::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'User Management';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Instructor Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'email')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('instructor_id')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(fn () => 'INS' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('qualification')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('specialization')
                            ->rows(2),
                        Forms\Components\TextInput::make('years_of_experience')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Forms\Components\TextInput::make('linkedin_url')
                            ->url()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Employment Details')
                    ->schema([
                        Forms\Components\Select::make('employment_type')
                            ->options([
                                'full-time' => 'Full Time',
                                'part-time' => 'Part Time',
                                'contract' => 'Contract',
                            ])
                            ->required()
                            ->native(false)
                            ->default('full-time'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'on-leave' => 'On Leave',
                            ])
                            ->required()
                            ->native(false)
                            ->default('active'),
                        Forms\Components\TextInput::make('hourly_rate')
                            ->numeric()
                            ->prefix('₦')
                            ->step(0.01),
                        Forms\Components\DatePicker::make('hire_date')
                            ->required()
                            ->native(false)
                            ->default(now()),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Biography')
                    ->schema([
                        Forms\Components\RichEditor::make('bio')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('instructor_id')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('user.full_name')
                    ->label('Name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('qualification')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('years_of_experience')
                    ->label('Experience')
                    ->suffix(' years')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('employment_type')
                    ->colors([
                        'success' => 'full-time',
                        'warning' => 'part-time',
                        'info' => 'contract',
                    ]),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'inactive',
                        'warning' => 'on-leave',
                    ]),
                Tables\Columns\TextColumn::make('hourly_rate')
                    ->money('NGN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('courses_count')
                    ->counts('courses')
                    ->label('Courses')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'on-leave' => 'On Leave',
                    ]),
                Tables\Filters\SelectFilter::make('employment_type')
                    ->options([
                        'full-time' => 'Full Time',
                        'part-time' => 'Part Time',
                        'contract' => 'Contract',
                    ]),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CoursesRelationManager::class,
            RelationManagers\ClassSessionsRelationManager::class,
            RelationManagers\AssignmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInstructors::route('/'),
            'create' => Pages\CreateInstructor::route('/create'),
            'edit' => Pages\EditInstructor::route('/{record}/edit'),
            'view' => Pages\ViewInstructor::route('/{record}'),
        ];
    }
}

// ============================================
// 4. PARENT RESOURCE
// ============================================

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ParentResource\Pages;
use App\Filament\Admin\Resources\ParentResource\RelationManagers;
use App\Models\ParentModel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ParentResource extends Resource
{
    protected static ?string $model = ParentModel::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $navigationGroup = 'User Management';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationLabel = 'Parents';
    protected static ?string $pluralLabel = 'Parents';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Parent Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'email')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('parent_id')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(fn () => 'PAR' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('occupation')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('secondary_phone')
                            ->tel()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Contact Information')
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->rows(2)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('city')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('state')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('country')
                            ->default('Nigeria')
                            ->maxLength(255),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Preferences')
                    ->schema([
                        Forms\Components\Select::make('preferred_contact_method')
                            ->options([
                                'email' => 'Email',
                                'phone' => 'Phone',
                                'sms' => 'SMS',
                            ])
                            ->required()
                            ->native(false)
                            ->default('email'),
                        Forms\Components\Toggle::make('receives_weekly_report')
                            ->default(true)
                            ->inline(false),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('parent_id')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('user.full_name')
                    ->label('Name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('user.phone')
                    ->label('Phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('occupation')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('children_count')
                    ->counts('children')
                    ->label('Children')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('preferred_contact_method')
                    ->colors([
                        'primary' => 'email',
                        'success' => 'phone',
                        'warning' => 'sms',
                    ]),
                Tables\Columns\IconColumn::make('receives_weekly_report')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('preferred_contact_method')
                    ->options([
                        'email' => 'Email',
                        'phone' => 'Phone',
                        'sms' => 'SMS',
                    ]),
                Tables\Filters\TernaryFilter::make('receives_weekly_report')
                    ->label('Receives Weekly Reports'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ChildrenRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListParents::route('/'),
            'create' => Pages\CreateParent::route('/create'),
            'edit' => Pages\EditParent::route('/{record}/edit'),
            'view' => Pages\ViewParent::route('/{record}'),
        ];
    }
}

// ============================================
// 5. COURSE RESOURCE
// ============================================

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CourseResource\Pages;
use App\Filament\Admin\Resources\CourseResource\RelationManagers;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Academic';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Course Information')
                    ->schema([
                        Forms\Components\TextInput::make('course_code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('category')
                            ->options([
                                'academic' => 'Academic',
                                'programming' => 'Programming',
                                'data-analysis' => 'Data Analysis',
                                'tax-audit' => 'Tax & Audit',
                                'business' => 'Business',
                                'counseling' => 'Counseling',
                                'other' => 'Other',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\Select::make('level')
                            ->options([
                                'beginner' => 'Beginner',
                                'intermediate' => 'Intermediate',
                                'advanced' => 'Advanced',
                            ])
                            ->required()
                            ->native(false)
                            ->default('beginner'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'active' => 'Active',
                                'archived' => 'Archived',
                            ])
                            ->required()
                            ->native(false)
                            ->default('draft'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Course Details')
                    ->schema([
                        Forms\Components\RichEditor::make('description')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('duration_weeks')
                            ->numeric()
                            ->default(12)
                            ->minValue(1)
                            ->suffix('weeks'),
                        Forms\Components\TextInput::make('credit_hours')
                            ->numeric()
                            ->default(3)
                            ->minValue(1),
                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->prefix('₦')
                            ->default(0)
                            ->step(0.01),
                        Forms\Components\TextInput::make('max_students')
                            ->numeric()
                            ->minValue(1),
                        Forms\Components\FileUpload::make('thumbnail')
                            ->image()
                            ->directory('course-thumbnails')
                            ->imageEditor(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Learning Objectives')
                    ->schema([
                        Forms\Components\Textarea::make('learning_objectives')
                            ->rows(3)
                            ->helperText('Enter learning objectives (JSON format or text)'),
                        Forms\Components\Textarea::make('prerequisites')
                            ->rows(3)
                            ->helperText('Enter prerequisites (JSON format or text)'),
                    ])
                    ->columns(1),
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
                    ->limit(30),
                Tables\Columns\BadgeColumn::make('category')
                    ->colors([
                        'primary' => 'academic',
                        'success' => 'programming',
                        'info' => 'data-analysis',
                        'warning' => 'tax-audit',
                        'danger' => 'business',
                    ]),
                Tables\Columns\BadgeColumn::make('level')
                    ->colors([
                        'success' => 'beginner',
                        'warning' => 'intermediate',
                        'danger' => 'advanced',
                    ]),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'active',
                        'danger' => 'archived',
                    ]),
                Tables\Columns\TextColumn::make('duration_weeks')
                    ->suffix(' weeks')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('NGN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('enrollments_count')
                    ->counts('enrollments')
                    ->label('Enrolled')
                    ->sortable(),
                Tables\Columns\TextColumn::make('instructors_count')
                    ->counts('instructors')
                    ->label('Instructors')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category'),
                Tables\Filters\SelectFilter::make('level'),
                Tables\Filters\SelectFilter::make('status'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\InstructorsRelationManager::class,
            RelationManagers\EnrollmentsRelationManager::class,
            RelationManagers\ClassSessionsRelationManager::class,
            RelationManagers\AssignmentsRelationManager::class,
            RelationManagers\MaterialsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
            'view' => Pages\ViewCourse::route('/{record}'),
        ];
    }
}

// ============================================
// 6. ENROLLMENT RESOURCE
// ============================================

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\EnrollmentResource\Pages;
use App\Models\Enrollment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EnrollmentResource extends Resource
{
    protected static ?string $model = Enrollment::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Academic';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Enrollment Details')
                    ->schema([
                        Forms\Components\Select::make('student_id')
                            ->relationship('student', 'student_id')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('course_id')
                            ->relationship('course', 'title')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\DatePicker::make('enrolled_at')
                            ->required()
                            ->native(false)
                            ->default(now()),
                        Forms\Components\DatePicker::make('completed_at')
                            ->native(false),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'completed' => 'Completed',
                                'dropped' => 'Dropped',
                                'failed' => 'Failed',
                            ])
                            ->required()
                            ->native(false)
                            ->default('active'),
                        Forms\Components\TextInput::make('progress_percentage')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'),
                        Forms\Components\TextInput::make('final_grade')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.student_id')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('student.user.full_name')
                    ->label('Student Name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('course.course_code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('course.title')
                    ->label('Course')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'info' => 'completed',
                        'warning' => 'dropped',
                        'danger' => 'failed',
                    ]),
                Tables\Columns\ProgressColumn::make('progress_percentage')
                    ->label('Progress'),
                Tables\Columns\TextColumn::make('final_grade')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('enrolled_at')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('completed_at')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'dropped' => 'Dropped',
                        'failed' => 'Failed',
                    ]),
                Tables\Filters\SelectFilter::make('course')
                    ->relationship('course', 'title'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEnrollments::route('/'),
            'create' => Pages\CreateEnrollment::route('/create'),
            'edit' => Pages\EditEnrollment::route('/{record}/edit'),
        ];
    }
}

// ============================================
// 7. CLASS SESSION RESOURCE
// ============================================

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ClassSessionResource\Pages;
use App\Models\ClassSession;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ClassSessionResource extends Resource
{
    protected static ?string $model = ClassSession::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Academic';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'Class Sessions';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Session Details')
                    ->schema([
                        Forms\Components\Select::make('course_id')
                            ->relationship('course', 'title')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('instructor_id')
                            ->relationship('instructor', 'instructor_id')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Schedule')
                    ->schema([
                        Forms\Components\DateTimePicker::make('scheduled_at')
                            ->required()
                            ->native(false),
                        Forms\Components\DateTimePicker::make('started_at')
                            ->native(false),
                        Forms\Components\DateTimePicker::make('ended_at')
                            ->native(false),
                        Forms\Components\TextInput::make('duration_minutes')
                            ->numeric()
                            ->suffix('minutes')
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Online Meeting')
                    ->schema([
                        Forms\Components\TextInput::make('google_meet_link')
                            ->url()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('google_calendar_event_id')
                            ->maxLength(255)
                            ->disabled(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'scheduled' => 'Scheduled',
                                'in-progress' => 'In Progress',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->native(false)
                            ->default('scheduled'),
                        Forms\Components\TextInput::make('max_participants')
                            ->numeric()
                            ->minValue(1),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Additional Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
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
                    ->limit(30),
                Tables\Columns\TextColumn::make('course.course_code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('instructor.user.full_name')
                    ->label('Instructor')
                    ->searchable(['first_name', 'last_name']),
                Tables\Columns\TextColumn::make('scheduled_at')
                    ->dateTime()
                    ->sortable(),
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
                    ->toggleable(),
                Tables\Columns\IconColumn::make('google_meet_link')
                    ->boolean()
                    ->label('Has Meet Link')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('attendances_count')
                    ->counts('attendances')
                    ->label('Attendance')
                    ->sortable(),
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
                Tables\Filters\SelectFilter::make('instructor')
                    ->relationship('instructor.user', 'first_name'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('start')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (ClassSession $record) => $record->startSession())
                    ->visible(fn (ClassSession $record) => $record->status === 'scheduled'),
                Tables\Actions\Action::make('end')
                    ->icon('heroicon-o-stop')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (ClassSession $record) => $record->endSession())
                    ->visible(fn (ClassSession $record) => $record->status === 'in-progress'),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
// 8. ASSIGNMENT RESOURCE
// ============================================

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AssignmentResource\Pages;
use App\Filament\Admin\Resources\AssignmentResource\RelationManagers;
use App\Models\Assignment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AssignmentResource extends Resource
{
    protected static ?string $model = Assignment::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Academic';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Assignment Details')
                    ->schema([
                        Forms\Components\Select::make('course_id')
                            ->relationship('course', 'title')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('instructor_id')
                            ->relationship('instructor', 'instructor_id')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
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
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Description & Instructions')
                    ->schema([
                        Forms\Components\RichEditor::make('description')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('instructions')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Grading & Deadlines')
                    ->schema([
                        Forms\Components\DateTimePicker::make('assigned_at')
                            ->required()
                            ->native(false)
                            ->default(now()),
                        Forms\Components\DateTimePicker::make('due_at')
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('max_score')
                            ->numeric()
                            ->required()
                            ->default(100)
                            ->minValue(1),
                        Forms\Components\Toggle::make('allows_late_submission')
                            ->default(false)
                            ->inline(false),
                        Forms\Components\TextInput::make('late_penalty_percentage')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->visible(fn (Forms\Get $get) => $get('allows_late_submission')),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status & Attachments')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                                'closed' => 'Closed',
                            ])
                            ->required()
                            ->native(false)
                            ->default('draft'),
                        Forms\Components\FileUpload::make('attachments')
                            ->multiple()
                            ->directory('assignment-attachments')
                            ->columnSpanFull(),
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
                    ->limit(30),
                Tables\Columns\TextColumn::make('course.course_code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('instructor.user.full_name')
                    ->label('Instructor')
                    ->searchable(['first_name', 'last_name']),
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
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_score')
                    ->sortable(),
                Tables\Columns\TextColumn::make('submissions_count')
                    ->counts('submissions')
                    ->label('Submissions')
                    ->sortable(),
                Tables\Columns\IconColumn::make('allows_late_submission')
                    ->boolean()
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
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
// 9. SERVICE REQUEST RESOURCE
// ============================================

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ServiceRequestResource\Pages;
use App\Models\ServiceRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceRequestResource extends Resource
{
    protected static ?string $model = ServiceRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationGroup = 'Services';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Request Details')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'email')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('service_type')
                            ->options([
                                'tax-calculation' => 'Tax Calculation',
                                'audit-help' => 'Audit Help',
                                'business-setup' => 'Business Setup',
                                'business-monitoring' => 'Business Monitoring',
                                'counseling' => 'Counseling',
                                'foreign-shopping' => 'Foreign Shopping',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status & Assignment')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'in-progress' => 'In Progress',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->native(false)
                            ->default('pending'),
                        Forms\Components\Select::make('priority')
                            ->options([
                                'low' => 'Low',
                                'normal' => 'Normal',
                                'high' => 'High',
                                'urgent' => 'Urgent',
                            ])
                            ->required()
                            ->native(false)
                            ->default('normal'),
                        Forms\Components\Select::make('assigned_to')
                            ->relationship('assignedUser', 'email')
                            ->searchable()
                            ->preload(),
                        Forms\Components\DateTimePicker::make('requested_at')
                            ->required()
                            ->native(false)
                            ->default(now()),
                        Forms\Components\DateTimePicker::make('completed_at')
                            ->native(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Cost Information')
                    ->schema([
                        Forms\Components\TextInput::make('estimated_cost')
                            ->numeric()
                            ->prefix('₦')
                            ->step(0.01),
                        Forms\Components\TextInput::make('final_cost')
                            ->numeric()
                            ->prefix('₦')
                            ->step(0.01),
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Additional Details')
                    ->schema([
                        Forms\Components\KeyValue::make('details')
                            ->columnSpanFull(),
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
                    ->limit(30),
                Tables\Columns\TextColumn::make('user.full_name')
                    ->label('Requested By')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('service_type')
                    ->colors([
                        'primary' => 'tax-calculation',
                        'success' => 'business-setup',
                        'info' => 'counseling',
                        'warning' => 'foreign-shopping',
                    ]),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'in-progress',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ]),
                Tables\Columns\BadgeColumn::make('priority')
                    ->colors([
                        'success' => 'low',
                        'info' => 'normal',
                        'warning' => 'high',
                        'danger' => 'urgent',
                    ]),
                Tables\Columns\TextColumn::make('assignedUser.full_name')
                    ->label('Assigned To')
                    ->searchable(['first_name', 'last_name'])
                    ->toggleable(),
                Tables\Columns\TextColumn::make('estimated_cost')
                    ->money('NGN')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('requested_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('service_type'),
                Tables\Filters\SelectFilter::make('status'),
                Tables\Filters\SelectFilter::make('priority'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServiceRequests::route('/'),
            'create' => Pages\CreateServiceRequest::route('/create'),
            'edit' => Pages\EditServiceRequest::route('/{record}/edit'),
            'view' => Pages\ViewServiceRequest::route('/{record}'),
        ];
    }
}

// ============================================
// 10. ADMIN DASHBOARD WIDGETS
// ============================================

namespace App\Filament\Admin\Widgets;

use App\Models\Student;
use App\Models\Instructor;
use App\Models\Course;
use App\Models\Enrollment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Students', Student::where('enrollment_status', 'active')->count())
                ->description('Active students')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success')
                ->chart([7, 3, 4, 5, 6, 3, 5]),
            
            Stat::make('Total Instructors', Instructor::where('status', 'active')->count())
                ->description('Active instructors')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info'),
            
            Stat::make('Active Courses', Course::where('status', 'active')->count())
                ->description('Published courses')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('warning'),
            
            Stat::make('Total Enrollments', Enrollment::where('status', 'active')->count())
                ->description('Active enrollments')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary'),
        ];
    }
}

// ============================================
// ADMIN DASHBOARD PAGE
// ============================================

namespace App\Filament\Admin\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    
    public function getWidgets(): array
    {
        return [
            \App\Filament\Admin\Widgets\StatsOverviewWidget::class,
        ];
    }
}

/*
┌─────────────────────────────────────────────────────────────────┐
│           FILAMENT ADMIN PANEL COMPLETE                          │
└─────────────────────────────────────────────────────────────────┘

✅ RESOURCES CREATED:
├── 1. UserResource (Users management)
├── 2. StudentResource (Students with relations)
├── 3. InstructorResource (Instructors with relations)
├── 4. ParentResource (Parents with children)
├── 5. CourseResource (Courses with full details)
├── 6. EnrollmentResource