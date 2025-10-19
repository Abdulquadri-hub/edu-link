<?php

namespace App\Filament\Admin\Resources\Enrollments\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;

class EnrollmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Enrollment Details')
                    ->schema([
                        Select::make('student_id')
                            ->relationship('student', 'student_id')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('course_id')
                            ->relationship('course', 'title')
                            ->required()
                            ->searchable()
                            ->preload(),
                        DatePicker::make('enrolled_at')
                            ->required()
                            ->native(false)
                            ->default(now()),
                        DatePicker::make('completed_at')
                            ->native(false),
                        Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'completed' => 'Completed',
                                'dropped' => 'Dropped',
                                'failed' => 'Failed',
                            ])
                            ->required()
                            ->native(false)
                            ->default('active'),
                        TextInput::make('progress_percentage')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'),
                        TextInput::make('final_grade')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                        Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
