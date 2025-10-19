<?php

namespace App\Filament\Admin\Resources\ClassSessions\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DateTimePicker;

class ClassSessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                 Section::make('Session Details')
                    ->schema([
                        Select::make('course_id')
                            ->relationship('course', 'title')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('instructor_id')
                            ->relationship('instructor', 'instructor_id')
                            ->required()
                            ->searchable()
                            ->preload(),
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Schedule')
                    ->schema([
                        DateTimePicker::make('scheduled_at')
                            ->required()
                            ->native(false),
                        DateTimePicker::make('started_at')
                            ->native(false),
                        DateTimePicker::make('ended_at')
                            ->native(false),
                        TextInput::make('duration_minutes')
                            ->numeric()
                            ->suffix('minutes')
                            ->disabled(),
                    ])
                    ->columns(2),

                Section::make('Online Meeting')
                    ->schema([
                        TextInput::make('google_meet_link')
                            ->url()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('google_calendar_event_id')
                            ->maxLength(255)
                            ->disabled(),
                        Select::make('status')
                            ->options([
                                'scheduled' => 'Scheduled',
                                'in-progress' => 'In Progress',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->native(false)
                            ->default('scheduled'),
                        TextInput::make('max_participants')
                            ->numeric()
                            ->minValue(1),
                    ])
                    ->columns(2),

                Section::make('Additional Notes')
                    ->schema([
                        Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
