<?php

namespace App\Filament\Instructor\Resources\Courses\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Infolists\Components\TextEntry;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                 Section::make('Course Information')
                    ->description('View your assigned course details')
                    ->schema([
                        TextInput::make('course_code')
                            ->disabled(),
                        TextInput::make('title')
                            ->disabled(),
                        Select::make('category')
                            ->disabled(),
                        Select::make('level')
                            ->disabled(),
                        TextEntry::make('enrolled_count')
                            ->label('Enrolled Students')
                            ->state(fn ($record) => $record->activeEnrollments()->count()),
                    ])
                    ->columns(2),

                Section::make('Course Content')
                    ->schema([
                        RichEditor::make('description')
                            ->disabled()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
