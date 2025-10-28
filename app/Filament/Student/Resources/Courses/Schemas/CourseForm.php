<?php

namespace App\Filament\Student\Resources\Courses\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('course_code')
                    ->required(),
                TextInput::make('title')
                    ->required(),
                Textarea::make('description')
                    ->default(null)
                    ->columnSpanFull(),
                Select::make('category')
                    ->options([
            'academic' => 'Academic',
            'programming' => 'Programming',
            'data-analyts' => 'Data analyts',
            'tax-audit' => 'Tax audit',
            'business' => 'Business',
            'counseling' => 'Counseling',
            'other' => 'Other',
        ])
                    ->default('academic')
                    ->required(),
                Select::make('level')
                    ->options(['beginner' => 'Beginner', 'intermidiate' => 'Intermidiate', 'advanced' => 'Advanced'])
                    ->default('beginner')
                    ->required(),
                TextInput::make('duration_weeks')
                    ->required()
                    ->numeric()
                    ->default(12),
                TextInput::make('credit_hours')
                    ->required()
                    ->numeric()
                    ->default(3),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->prefix('$'),
                TextInput::make('thumbnail')
                    ->default(null),
                Textarea::make('learning_objectives')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('prerequisites')
                    ->default(null)
                    ->columnSpanFull(),
                Select::make('status')
                    ->options(['draft' => 'Draft', 'active' => 'Active', 'archived' => 'Archived'])
                    ->default('draft')
                    ->required(),
                TextInput::make('max_students')
                    ->numeric()
                    ->default(null),
            ]);
    }
}
