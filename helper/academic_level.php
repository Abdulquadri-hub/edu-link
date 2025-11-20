

## Step 8: Update Existing Course Form to Include Academic Level

**File: `app/Filament/Admin/Resources/Courses/Schemas/CourseForm.php`**

```php
<?php

namespace App\Filament\Admin\Resources\Courses\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Course Information')
                    ->schema([
                        TextInput::make('course_code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        
                        // NEW: Academic Level Selector
                        Select::make('academic_level_id')
                            ->label('Academic Level (Grade)')
                            ->relationship('academicLevel', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('Select the grade level this course is designed for')
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Auto-populate level field based on academic level
                                if ($state) {
                                    $academicLevel = \App\Models\AcademicLevel::find($state);
                                    if ($academicLevel) {
                                        $gradeNumber = $academicLevel->grade_number;
                                        if ($gradeNumber <= 7) {
                                            $set('level', 'beginner');
                                        } elseif ($gradeNumber <= 10) {
                                            $set('level', 'intermediate');
                                        } else {
                                            $set('level', 'advanced');
                                        }
                                    }
                                }
                            }),
                        
                        Select::make('category')
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
                        
                        Select::make('level')
                            ->options([
                                'beginner' => 'Beginner',
                                'intermediate' => 'Intermediate',
                                'advanced' => 'Advanced',
                            ])
                            ->required()
                            ->native(false)
                            ->default('beginner')
                            ->helperText('This is auto-filled based on grade level but can be adjusted'),
                        
                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'active' => 'Active',
                                'archived' => 'Archived',
                            ])
                            ->required()
                            ->native(false)
                            ->default('draft'),
                        
                        Textarea::make('learning_objectives')
                            ->rows(3)
                            ->helperText('Enter learning objectives (JSON format or text)')
                            ->columnSpanFull(),
                        
                        Textarea::make('prerequisites')
                            ->rows(3)
                            ->helperText('Enter prerequisites (JSON format or text)')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Section::make('Course Details')
                    ->schema([
                        RichEditor::make('description')
                            ->columnSpanFull(),
                        
                        TextInput::make('duration_weeks')
                            ->numeric()
                            ->default(12)
                            ->minValue(1)
                            ->suffix('weeks'),
                        
                        TextInput::make('credit_hours')
                            ->numeric()
                            ->default(3)
                            ->minValue(1),
                        
                        TextInput::make('price')
                            ->numeric()
                            ->prefix('₦')
                            ->default(0)
                            ->step(0.01)
                            ->helperText('Leave at 0 for grade-based pricing'),
                        
                        TextInput::make('max_students')
                            ->numeric()
                            ->minValue(1),
                        
                        FileUpload::make('thumbnail')
                            ->image()
                            ->directory('course-thumbnails')
                            ->imageEditor()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
```

---

## Step 9: Update Student Form to Include Academic Level

**File: `app/Filament/Admin/Resources/Students/Schemas/StudentForm.php`**

```php
<?php

namespace App\Filament\Admin\Resources\Students\Schemas;

use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\DateTimePicker;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Student Information')
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'email',
                               fn ($query) => $query->where('user_type', 'student')
                            )
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Section::make("Personal Information")->schema([
                                    TextInput::make('first_name')
                                        ->required()
                                        ->maxLength(255),
                                    TextInput::make('last_name')
                                        ->required()
                                        ->maxLength(255),
                                    TextInput::make('username')
                                        ->required()
                                        ->unique(ignoreRecord: true)
                                        ->maxLength(255),
                                    TextInput::make('email')   
                                        ->email()
                                        ->autocomplete(true)
                                        ->required()
                                        ->unique(ignoreRecord: true)
                                        ->maxLength(255),
                                    TextInput::make('phone')
                                       ->tel()
                                       ->maxLength(255),
                                    FileUpload::make('avatar')
                                        ->image()
                                        ->directory('avatars')
                                        ->imageEditor(true),
                                ])
                                ->columns(2),
                
                                Section::make("Account Settings")->schema([
                                    Select::make('user_type')->options([
                                        'admin' => 'Admin',
                                        'instructor' => 'Instructor',
                                        'student' => 'Student',
                                        'parent' => 'Parent'
                                    ])
                                    ->required()
                                    ->native(false)
                                    ->searchable(),
                
                                    Select::make('status')->options([
                                        'active' => 'Active',
                                        'inactive' => 'Inactive',
                                        'suspended' => 'Suspended'
                                    ])
                                    ->required()
                                    ->native(false)
                                    ->default('active')
                                    ->searchable(),
                
                                    TextInput::make('password')
                                       ->password()
                                       ->dehydrateStateUsing(
                                            fn ($state) => Hash::make($state)
                                        )
                                        ->dehydrated(fn ($state) => filled($state))
                                        ->required(fn (string $context) : bool => $context === 'create')
                                        ->maxLength(255),
                
                                    DateTimePicker::make('email_verified_at')
                                        ->native(false),
                                ])
                                ->columns(2),
                            ]),
                        
                        TextInput::make('student_id')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(fn () => 'STU' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT))
                            ->maxLength(255),
                        
                        // NEW: Academic Level Selector
                        Select::make('academic_level_id')
                            ->label('Current Grade Level')
                            ->relationship('academicLevel', 'name', fn ($query) => $query->active()->ordered())
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('Select the student\'s current grade level')
                            ->native(false),
                        
                        DatePicker::make('date_of_birth')
                            ->required()
                            ->native(false)
                            ->maxDate(now()),
                        
                        Select::make('gender')
                            ->options([
                                'male' => 'Male',
                                'female' => 'Female',
                                'other' => 'Other',
                            ])
                            ->required()
                            ->native(false),
                        
                        DatePicker::make('enrollment_date')
                            ->required()
                            ->native(false)
                            ->default(now()),
                        
                        Select::make('enrollment_status')
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

                Section::make('Contact Information')
                    ->schema([
                        Textarea::make('address')
                            ->rows(2)
                            ->columnSpanFull(),
                        TextInput::make('city')
                            ->maxLength(255),
                        TextInput::make('state')
                            ->maxLength(255),
                        TextInput::make('country')
                            ->default('Nigeria')
                            ->maxLength(255),
                    ])
                    ->columns(3),

                Section::make('Emergency Contact')
                    ->schema([
                        TextInput::make('emergency_contact_name')
                            ->maxLength(255),
                        TextInput::make('emergency_contact_phone')
                            ->tel()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Additional Notes')
                    ->schema([
                        RichEditor::make('notes')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
```

---

## Step 10: Update Students Table to Display Academic Level

**File: `app/Filament/Admin/Resources/Students/Tables/StudentsTable.php`**

```php
<?php

namespace App\Filament\Admin\Resources\Students\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\ForceDeleteBulkAction;

class StudentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student_id')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                
                TextColumn::make('user.first_name')
                    ->label('First Name')
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('user.last_name')
                    ->label('Last Name')
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('user.email')
                    ->label('Email')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                
                // NEW: Academic Level Column
                TextColumn::make('academicLevel.name')
                    ->label('Grade Level')
                    ->badge()
                    ->color(fn ($record) => match($record->academicLevel?->level_type) {
                        'elementary' => 'success',
                        'middle' => 'warning',
                        'high' => 'danger',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('date_of_birth')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('gender')
                    ->badge()
                    ->colors([
                        'primary' => 'male',
                        'success'  => 'female',
                        'warning' => 'other'
                    ])
                    ->toggleable(),
                
                TextColumn::make('enrollment_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('enrollment_status')
                    ->badge()
                    ->colors([
                        'success' => 'active',
                        'info' => 'graduated',
                        'warning' => 'dropped',
                        'danger' => 'suspended',
                    ]),
                
                TextColumn::make('enrollments_count')
                    ->counts('enrollments')
                    ->label('Courses')
                    ->sortable(),
                
                TextColumn::make('parents_count')
                    ->counts('parents')
                    ->label('Parents')
                    ->sortable(),
                
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // NEW: Filter by Academic Level
                SelectFilter::make('academic_level_id')
                    ->label('Grade Level')
                    ->relationship('academicLevel', 'name')
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('enrollment_status')
                    ->options([
                        'active' => 'Active',
                        'graduated' => 'Graduated',
                        'dropped' => 'Dropped',
                        'suspended' => 'Suspended',
                    ]),
                
                SelectFilter::make('gender'),
                
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
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
```

---

## Step 11: Update Courses Table to Display Academic Level

**File: `app/Filament/Admin/Resources/Courses/Tables/CoursesTable.php`**

```php
<?php

namespace App\Filament\Admin\Resources\Courses\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;

class CoursesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail')
                    ->circular(),
                
                TextColumn::make('course_code')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                
                // NEW: Academic Level Column
                TextColumn::make('academicLevel.name')
                    ->label('Grade Level')
                    ->badge()
                    ->color(fn ($record) => match($record->academicLevel?->level_type) {
                        'elementary' => 'success',
                        'middle' => 'warning',
                        'high' => 'danger',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('category')
                    ->badge()
                    ->colors([
                        'primary' => 'academic',
                        'success' => 'programming',
                        'info' => 'data-analysis',
                        'warning' => 'tax-audit',
                        'danger' => 'business',
                    ]),
                
                TextColumn::make('level')
                    ->badge()
                    ->colors([
                        'success' => 'beginner',
                        'warning' => 'intermediate',
                        'danger' => 'advanced',
                    ]),
                
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'active',
                        'danger' => 'archived',
                    ]),
                
                TextColumn::make('duration_weeks')
                    ->suffix(' weeks')
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('price')
                    ->money('NGN')
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('enrollments_count')
                    ->counts('enrollments')
                    ->label('Enrolled')
                    ->sortable(),
                
                TextColumn::make('instructors_count')
                    ->counts('instructors')
                    ->label('Instructors')
                    ->sortable(),
            ])
            ->filters([
                // NEW: Filter by Academic Level
                SelectFilter::make('academic_level_id')
                    ->label('Grade Level')
                    ->relationship('academicLevel', 'name')
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('category'),
                SelectFilter::make('level'),
                SelectFilter::make('status'),
                TrashedFilter::make()
            ])
            ->recordActions([
                EditAction::make(),
                ViewAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
