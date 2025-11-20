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
                                    FileUpload::make('avatar')->image()->directory('avatars')->imageEditor(true),
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
                            
                        Select::make('academic_level_id')
                            ->label('Current Grade Level')
                            ->relationship('academicLevel', 'name', fn ($query) => $query->active()->ordered())
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('Select the student\'s current grade level')
                            ->native(false),  

                        TextInput::make('student_id')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(fn () => 'STU' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT))
                            ->maxLength(255),
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
