<?php

namespace App\Filament\Instructor\Resources\ParentUploadedAssignments\Tables;

use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class ParentUploadedAssignmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.user.full_name')
                    ->label('Student')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                
                TextColumn::make('parent.user.full_name')
                    ->label('Uploaded By (Parent)')
                    ->searchable(['first_name', 'last_name'])
                    ->description(fn ($record) => 'Parent'),
                
                TextColumn::make('assignment.title')
                    ->label('Assignment')
                    ->searchable()
                    ->limit(40)
                    ->description(fn ($record) => $record->assignment->course->course_code),
                
                TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->description(fn ($record) => $record->submitted_at->diffForHumans()),
                
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'info' => 'submitted',
                        'success' => 'graded',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                
                TextColumn::make('submission.grade.percentage')
                    ->label('Grade')
                    ->suffix('%')
                    ->color('success')
                    ->weight('bold')
                    ->placeholder('Not graded')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('course')
                    ->label('Course')
                    ->relationship('assignment.course', 'title')
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('status')
                    ->options([
                        'submitted' => 'Awaiting Grading',
                        'graded' => 'Graded',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                
                Action::make('grade')
                    ->label('Grade')
                    ->icon('heroicon-o-academic-cap')
                    ->color('success')
                    ->url(fn ($record) => route('filament.instructor.resources.submissions.view', [
                        'record' => $record->submission_id
                    ]))
                    ->visible(fn ($record) => $record->submission_id && !$record->submission->grade),
            ])
            ->toolbarActions([])
            ->defaultSort('submitted_at', 'desc');
    }
}
