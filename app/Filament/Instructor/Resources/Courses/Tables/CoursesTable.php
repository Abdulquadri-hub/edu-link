<?php

namespace App\Filament\Instructor\Resources\Courses\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\ForceDeleteBulkAction;

class CoursesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail')
                    ->circular(),
                TextColumn::make('course_code')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->wrap(),
                TextColumn::make('category')
                    ->badge()
                    ->colors([
                        'primary' => 'academic',
                        'success' => 'programming',
                        'info' => 'data-analysis',
                        'warning' => 'tax-audit',
                    ]),
                TextColumn::make('level')
                   ->badge(),
                TextColumn::make('active_enrollments_count')
                    ->counts('activeEnrollments')
                    ->label('Students')
                    ->sortable(),
                TextColumn::make('class_sessions_count')
                    ->counts('classSessions')
                    ->label('Sessions')
                    ->sortable(),
                TextColumn::make('assignments_count')
                    ->counts('assignments')
                    ->label('Assignments')
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('category'),
                SelectFilter::make('level'),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }
}
