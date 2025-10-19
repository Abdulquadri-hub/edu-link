<?php

namespace App\Filament\Admin\Resources\Assignments\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Utilities\Get;

class AssignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Assignment Details')
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
                        Select::make('type')
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

                Section::make('Description & Instructions')
                    ->schema([
                        RichEditor::make('description')
                            ->required()
                            ->columnSpanFull(),
                        RichEditor::make('instructions')
                            ->columnSpanFull(),
                    ]),

                Section::make('Grading & Deadlines')
                    ->schema([
                        DateTimePicker::make('assigned_at')
                            ->required()
                            ->native(false)
                            ->default(now()),
                        DateTimePicker::make('due_at')
                            ->required()
                            ->native(false),
                        TextInput::make('max_score')
                            ->numeric()
                            ->required()
                            ->default(100)
                            ->minValue(1),
                        Toggle::make('allows_late_submission')
                            ->default(false)
                            ->inline(false),
                        TextInput::make('late_penalty_percentage')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->visible(fn (Get $get) => $get('allows_late_submission')),
                    ])
                    ->columns(2),

                Section::make('Status & Attachments')
                    ->schema([
                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                                'closed' => 'Closed',
                            ])
                            ->required()
                            ->native(false)
                            ->default('draft'),
                        FileUpload::make('attachments')
                            ->multiple()
                            ->directory('assignment-attachments')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
