<?php

namespace App\Filament\Admin\Resources\AcademicLevels\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class AcademicLevelsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Grade 1')
                            ->helperText('Display name for this academic level'),
                        
                        TextInput::make('grade_number')
                            ->required()
                            ->numeric()
                            ->unique(ignoreRecord: true)
                            ->minValue(1)
                            ->maxValue(12)
                            ->helperText('Numeric grade level (1-12)'),
                        
                        Select::make('level_type')
                            ->options([
                                'elementary' => 'Elementary (Grades 1-7)',
                                'middle' => 'Middle School (Grades 8-10)',
                                'high' => 'High School (Grades 11-12)',
                            ])
                            ->required()
                            ->native(false)
                            ->default('elementary'),
                        
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Order in which levels are displayed'),
                        
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->inline(false)
                            ->helperText('Only active levels are available for enrollment'),
                    ])
                    ->columns(2),

                Section::make('Description')
                    ->schema([
                        Textarea::make('description')
                            ->rows(3)
                            ->maxLength(500)
                            ->placeholder('Brief description of this grade level...')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
