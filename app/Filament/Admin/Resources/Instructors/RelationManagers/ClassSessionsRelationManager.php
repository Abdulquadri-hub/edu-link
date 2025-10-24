<?php

namespace App\Filament\Admin\Resources\Instructors\RelationManagers;

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

class ClassSessionsRelationManager extends RelationManager
{
    protected static string $relationship = 'classSessions';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('course_id')
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->limit(30),
                
                TextColumn::make('course.course_code')
                    ->searchable(),
                
                TextColumn::make('scheduled_at')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
                
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'info' => 'scheduled',
                        'warning' => 'in-progress',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ]),
                
                TextColumn::make('duration_minutes')
                    ->suffix(' min')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('scheduled_at', 'desc');
    }
}
