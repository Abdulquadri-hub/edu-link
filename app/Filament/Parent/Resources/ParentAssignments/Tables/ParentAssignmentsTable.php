<?php

namespace App\Filament\Parent\Resources\ParentAssignments\Tables;

use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Filters\Filter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class ParentAssignmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.user.full_name')
                    ->label('Child')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                
                TextColumn::make('assignment.title')
                    ->label('Assignment')
                    ->searchable()
                    ->limit(40)
                    ->description(fn ($record) => $record->assignment->course->course_code),
                
                TextColumn::make('assignment.course.title')
                    ->label('Course')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(),
                
                TextColumn::make('assignment.due_at')
                    ->label('Due Date')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->description(fn ($record) => $record->assignment->due_at->isPast() ? 'Overdue' : $record->assignment->due_at->diffForHumans())
                    ->color(fn ($record) => $record->assignment->due_at->isPast() ? 'danger' : 'success'),
                
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
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
                    ->visible(fn ($record) => $record?->status === 'graded'),
                
                TextColumn::make('uploaded_at')
                    ->label('Uploaded')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->description(fn ($record) => $record->uploaded_at->diffForHumans())
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('student')
                    ->label('Child')
                    ->relationship('student', 'student_id')
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'submitted' => 'Submitted',
                        'graded' => 'Graded',
                    ]),
                
                Filter::make('pending')
                    ->label('Pending Only')
                    ->query(fn (Builder $query) => $query->where('status', 'pending'))
                    // ->default(),
            ])
            ->recordActions([
                Action::make('submit')
                    ->label('Submit to Instructor')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Submit Assignment to Instructor')
                    ->modalDescription(fn ($record) => "Are you sure you want to submit this assignment for {$record->student->user->full_name}? Once submitted, you cannot edit it.")
                    ->modalSubmitActionLabel('Yes, Submit')
                    ->action(function ($record) {
                        try {
                            $record->submitToInstructor();
                            
                            Notification::make()
                                ->success()
                                ->title('Assignment Submitted')
                                ->body('The assignment has been submitted to the instructor for grading.')
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Submission Failed')
                                ->body('There was an error submitting the assignment. Please try again.')
                                ->send();
                        }
                    })
                    ->visible(fn ($record) => $record->status === 'pending'),
                
                ViewAction::make(),
                
                EditAction::make()
                    ->visible(fn ($record) => $record->status === 'pending'),
                
                DeleteAction::make()
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->requiresConfirmation(),
            ])
            ->toolbarActions([])
            ->defaultSort('uploaded_at', 'desc');
    }
}
