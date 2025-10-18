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
                            ->default('beginner'),
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
                            ->step(0.01),
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
