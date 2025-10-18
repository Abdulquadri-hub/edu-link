<?php

namespace App\Filament\Admin\Resources\Courses\RelationManagers;

use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DetachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Resources\RelationManagers\RelationManager;

class InstructorsRelationManager extends RelationManager
{
    protected static string $relationship = 'instructors';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Assign Instructor')
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'email', 
                               fn ($query) => $query->where('user_type', 'instructor')
                            )
                            ->required()
                            ->searchable()
                            ->preload(),
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user.full_name')
            ->columns([
                TextColumn::make('instructor_id')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('user.full_name')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('user.email')
                    ->searchable()
                    ->copyable(),
                
                TextColumn::make('qualification')
                    ->toggleable(),
                
                TextColumn::make('years_of_experience')
                    ->suffix(' years'),
                
                IconColumn::make('pivot.is_primary_instructor')
                    ->boolean()
                    ->label('Primary'),
                
                TextColumn::make('pivot.assigned_date')
                    ->label('Assigned Date')
                    ->date(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AttachAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DetachAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
