<?php

namespace App\Filament\Admin\Resources\Instructors\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;

class InstructorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Instructor Information')
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'email', 
                                fn ($query) => $query->where('user_type', 'instructor')
                            )
                            ->required()
                            ->searchable()
                            ->preload()
                             ->createOptionForm([
                                TextInput::make('first_name')->required(),
                                TextInput::make('last_name')->required(),
                                TextInput::make('email')->email()->required(),
                                TextInput::make('username')->required(),
                                TextInput::make('password')->password()->required(),
                            ]),
                        TextInput::make('instructor_id')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(fn () => 'INS' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT))
                            ->maxLength(255),
                        TextInput::make('qualification')
                            ->maxLength(255),
                        Textarea::make('specialization')
                            ->rows(2),
                        TextInput::make('years_of_experience')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        TextInput::make('linkedin_url')
                            ->url()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Employment Details')
                    ->schema([
                        Select::make('employment_type')
                            ->options([
                                'full-time' => 'Full Time',
                                'part-time' => 'Part Time',
                                'contract' => 'Contract',
                            ])
                            ->required()
                            ->native(false)
                            ->default('full-time'),
                        Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'on-leave' => 'On Leave',
                            ])
                            ->required()
                            ->native(false)
                            ->default('active'),
                        TextInput::make('hourly_rate')
                            ->numeric()
                            ->prefix('₦')
                            ->step(0.01),
                        DatePicker::make('hire_date')
                            ->required()
                            ->native(false)
                            ->default(now()),
                    ])
                    ->columns(2),

                Section::make('Biography')
                    ->schema([
                        RichEditor::make('bio')
                            ->columnSpanFull(),
                ]),
            ]);
    }
}
