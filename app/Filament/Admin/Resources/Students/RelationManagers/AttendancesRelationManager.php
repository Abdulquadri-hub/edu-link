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
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\DissociateBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Resources\RelationManagers\RelationManager;

class AttendancesRelationManager extends RelationManager
{
    protected static string $relationship = 'attendances';
        protected static ?string $recordTitleAttribute = 'classSession.title';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Attendance Details')
                    ->schema([
                        Select::make('class_session_id')
                            ->relationship('classSession', 'title')
                            ->required()
                            ->searchable()
                            ->preload(),
                        
                        Select::make('status')
                            ->options([
                                'present' => 'Present',
                                'absent' => 'Absent',
                                'late' => 'Late',
                                'excused' => 'Excused',
                            ])
                            ->required()
                            ->native(false)
                            ->default('absent'),
                        
                        DateTimePicker::make('joined_at')
                            ->native(false),
                        
                        DateTimePicker::make('left_at')
                            ->native(false),
                        
                        TextInput::make('duration_minutes')
                            ->numeric()
                            ->disabled(),
                        
                        Textarea::make('notes')
                            ->rows(2),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('class_session_id')
            ->columns([
                TextColumn::make('classSession.title')
                    ->searchable()
                    ->limit(30),
                
                TextColumn::make('classSession.scheduled_at')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
                
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'present',
                        'danger' => 'absent',
                        'warning' => 'late',
                        'info' => 'excused',
                    ]),
                
                TextColumn::make('joined_at')
                    ->dateTime('H:i')
                    ->toggleable(),
                
                TextColumn::make('duration_minutes')
                    ->suffix(' min')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'present' => 'Present',
                        'absent' => 'Absent',
                        'late' => 'Late',
                        'excused' => 'Excused',
                    ]),
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
