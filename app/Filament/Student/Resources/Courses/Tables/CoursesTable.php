<?php

namespace App\Filament\Student\Resources\Courses\Tables;

use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
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
                    ->circular()
                    ->defaultImageUrl(asset('images/default-course.png')),
                
                TextColumn::make('course_code')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->wrap()
                    ->description(fn ($record) => $record->category),
                
                TextColumn::make('instructors.user.full_name')
                    ->label('Instructor')
                    ->listWithLineBreaks()
                    ->limitList(2)
                    ->expandableLimitedList(),
                
                TextColumn::make('enrollments.progress_percentage')
                    ->label('Progress')
                    ->formatStateUsing(fn ($record) => $record->enrollments->first()?->progress_percentage . '%')
                    ->color('success')
                    ->weight('bold'),
                
                TextColumn::make('progress')
                    ->label('Progress Bar')
                    ->getStateUsing(fn ($record) => $record->enrollments->first()?->progress_percentage ?? 0),
                
                TextColumn::make('level')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'beginner' => 'success',
                        'intermediate' => 'warning',
                        'advanced' => 'danger',
                    }),
            ])
            ->filters([
                SelectFilter::make('category'),
                SelectFilter::make('level'),
            ])
            ->recordActions([
                ViewAction::make(),
                
                // Action::make('viewMaterials')
                //     ->label('Materials')
                //     ->icon('heroicon-o-folder')
                //     ->color('info')
                //     ->url(fn ($record) => route('filament.student.resources.materials.index', [
                //         'tableFilters' => ['course' => ['value' => $record->id]]
                //     ])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
