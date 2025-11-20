<?php

namespace App\Filament\Admin\Resources\AcademicLevels\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\ForceDeleteBulkAction;

class AcademicLevelsTable
{
     public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('grade_number')
                    ->label('Grade #')
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),
                
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->description),
                
                TextColumn::make('level_type')
                    ->badge()
                    ->colors([
                        'success' => 'elementary',
                        'warning' => 'middle',
                        'danger' => 'high',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                
                TextColumn::make('students_count')
                    ->counts('students')
                    ->label('Students')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                
                TextColumn::make('courses_count')
                    ->counts('courses')
                    ->label('Courses')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active')
                    ->sortable(),
                
                TextColumn::make('sort_order')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('level_type')
                    ->options([
                        'elementary' => 'Elementary',
                        'middle' => 'Middle School',
                        'high' => 'High School',
                    ]),
                
                SelectFilter::make('is_active')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ])
                    ->label('Status'),
                
                // TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc');
    }
}
