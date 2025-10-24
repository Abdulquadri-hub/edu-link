<?php

namespace App\Filament\Admin\Resources\Instructors\RelationManagers;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Resources\RelationManagers\RelationManager;
use App\Filament\Admin\Resources\Assignments\AssignmentResource;

class AssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'assignments';

    protected static ?string $relatedResource = AssignmentResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->limit(30),
                
                TextColumn::make('course.course_code')
                    ->searchable(),
                
                TextColumn::make('type')
                    ->badge(),
                
                TextColumn::make('status')
                   ->badge(),
                
                TextColumn::make('due_at')
                    ->dateTime('M d, Y')
                    ->sortable(),
                
                TextColumn::make('max_score'),
                
                TextColumn::make('submissions_count')
                    ->counts('submissions'),
            ])
            ->filters([
                SelectFilter::make('status'),
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
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
