<?php

namespace App\Filament\Instructor\Resources\Assignments\Tables;

use App\Models\Assignment;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use App\Events\AssignmentCreated;
use Filament\Actions\DeleteAction;
use Filament\Tables\Filters\Filter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\ForceDeleteBulkAction;

class AssignmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->wrap(),
                TextColumn::make('course.course_code')
                    ->searchable()
                    ->sortable()
                    ->label('Course'),
                TextColumn::make('type')
                    ->badge()
                    ->colors([
                        'primary' => 'quiz',
                        'success' => 'homework',
                        'info' => 'project',
                        'danger' => 'exam',
                        'warning' => 'other',
                    ]),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'published',
                        'danger' => 'closed',
                    ]),
                TextColumn::make('due_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->description(fn ($record) => $record->due_at->isPast() ? 'Overdue' : $record->due_at->diffForHumans()),
                TextColumn::make('max_score')
                    ->suffix(' pts')
                    ->sortable(),
                TextColumn::make('submissions_count')
                    ->counts('submissions')
                    ->label('Submissions')
                    ->sortable()
                    ->description(fn ($record) => $record->getGradedCount() . ' graded'),
                IconColumn::make('allows_late_submission')
                    ->boolean()
                    ->label('Late OK')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'closed' => 'Closed',
                    ]),
                SelectFilter::make('type')
                    ->options([
                        'quiz' => 'Quiz',
                        'homework' => 'Homework',
                        'project' => 'Project',
                        'exam' => 'Exam',
                        'other' => 'Other',
                    ]),
                SelectFilter::make('course')
                    ->relationship('course', 'title'),
                Filter::make('overdue')
                    ->query(fn (Builder $query) => $query->where('due_at', '<', now())->where('status', 'published'))
                    ->label('Overdue'),
            ])
            ->recordActions([
                Action::make('publish')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Publish Assignment')
                    ->modalDescription('Students will be notified and can start submitting.')
                    ->action(function (Assignment $record) {
                        $record->update(['status' => 'published']);

                        event(new AssignmentCreated($record));

                        Notification::make()
                            ->success()
                            ->title('Assignment published')
                            ->body('Students have been notified')
                            ->send();
                    })
                    ->visible(fn (Assignment $record) => $record->status === 'draft'),
                
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
            ->defaultSort('created_at', 'desc');
    }
}
