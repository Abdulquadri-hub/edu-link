<?php

namespace App\Filament\Instructor\Resources\Assignments\Schemas;

use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
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
                            ->relationship('course', 'title', function ($query) {
                                $query->whereHas('instructors', function ($q) {
                                    $q->where('instructor_course.instructor_id', Auth::user()->instructor->id);
                                });
                            })
                            ->required()
                            ->searchable()
                            ->preload(),
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Week 1 Quiz - Laravel Basics'),
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
                        Select::make('status')
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

                Section::make('Description & Instructions')
                    ->schema([
                        RichEditor::make('description')
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
                        RichEditor::make('instructions')
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

                Section::make('Grading & Deadlines')
                    ->schema([
                        DateTimePicker::make('assigned_at')
                            ->required()
                            ->native(false)
                            ->default(now())
                            ->label('Assigned Date'),
                        DateTimePicker::make('due_at')
                            ->required()
                            ->native(false)
                            ->minDate(now())
                            ->label('Due Date'),
                        TextInput::make('max_score')
                            ->numeric()
                            ->required()
                            ->default(100)
                            ->minValue(1)
                            ->suffix('points'),
                        Toggle::make('allows_late_submission')
                            ->default(false)
                            ->live()
                            ->label('Allow Late Submissions'),
                        TextInput::make('late_penalty_percentage')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->label('Late Penalty')
                            ->visible(fn (Get $get) => $get('allows_late_submission')),
                    ])
                    ->columns(2),

                Section::make('Attachments')
                    ->schema([
                        FileUpload::make('attachments')
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
}
