<?php

namespace App\Filament\Student\Resources\Grades\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Filters\Filter;
use Filament\Support\Enums\TextSize;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class GradesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('submission.assignment.title')
                    ->label('Assignment')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->description(fn ($record) => $record->submission->assignment->course->course_code),
                
                TextColumn::make('submission.assignment.course.title')
                    ->label('Course')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(),
                
                TextColumn::make('percentage')
                    ->label('Grade')
                    ->suffix('%')
                    ->sortable()
                    ->weight('bold')
                    ->size(TextSize::Large)
                    ->color(fn ($state) => match(true) {
                        $state >= 90 => 'success',
                        $state >= 80 => 'info',
                        $state >= 70 => 'warning',
                        default => 'danger',
                    }),
                
                TextColumn::make('letter_grade')
                    ->label('Letter')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'A' => 'success',
                        'B' => 'info',
                        'C' => 'warning',
                        default => 'danger',
                    }),
                
                TextColumn::make('score')
                    ->label('Score')
                    ->formatStateUsing(fn ($state, $record) => $state . ' / ' . $record->max_score)
                    ->sortable(),
                
                TextColumn::make('submission.is_late')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => $state ? 'Late' : 'On Time')
                    ->badge()
                    ->colors([
                        'danger' => true,
                        'success' => false,
                    ])
                    ->toggleable(),
                
                TextColumn::make('published_at')
                    ->label('Graded On')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->description(fn ($record) => $record->published_at->diffForHumans()),
                
                TextColumn::make('instructor.user.full_name')
                    ->label('Instructor')
                    ->searchable(['first_name', 'last_name'])
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('course')
                    ->label('Course')
                    ->relationship('submission.assignment.course', 'title')
                    ->searchable()
                    ->preload(),
                
                Filter::make('passing')
                    ->label('Passing (≥60%)')
                    ->query(fn (Builder $query) => $query->where('percentage', '>=', 60))
                    ->toggle(),
                
                Filter::make('failing')
                    ->label('Failing (<60%)')
                    ->query(fn (Builder $query) => $query->where('percentage', '<', 60))
                    ->toggle(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([])
            ->defaultSort('published_at', 'desc');
    }
}
