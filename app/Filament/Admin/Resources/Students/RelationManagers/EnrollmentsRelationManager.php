<?php

namespace App\Filament\Admin\Resources\Students\RelationManagers;


use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\DissociateBulkAction;
use Filament\Resources\RelationManagers\RelationManager;

class EnrollmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'enrollments';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Enrollment Details')
                    ->schema([
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
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0)
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('course_id')
            ->columns([
                TextColumn::make('course.course_code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('course.title')
                    ->searchable()
                    ->limit(30),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'active',
                        'info' => 'completed',
                        'warning' => 'dropped',
                        'danger' => 'failed',
                    ]),
                TextColumn::make('progress_percentage'),
                
                TextColumn::make('final_grade')
                    ->sortable()
                    ->placeholder('-'),
                
                TextColumn::make('enrolled_at')
                    ->date()
                    ->sortable(),
                
                TextColumn::make('completed_at')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'dropped' => 'Dropped',
                        'failed' => 'Failed',
                    ]),
            ])
            ->headerActions([
                CreateAction::make()
                   ->label('Enroll Student'),
            ])
            ->recordActions([
                DissociateAction::make()
                   ->label('UnEnroll Student'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make()
                        ->label('Bulk UnEnroll Student'),
                ]),
            ]);
    }
}
