<?php

namespace App\Filament\Instructor\Resources\Submissions\Tables;

use App\Models\Submission;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Query\Builder;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Infolists\Components\TextEntry;

class SubmissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.student_id')
                    ->searchable()
                    ->sortable()
                    ->label('Student ID')
                    ->copyable(),
                
                TextColumn::make('student.user.full_name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable()
                    ->label('Student Name')
                    ->description(fn ($record) => $record->student->user->email),
                
                TextColumn::make('assignment.title')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->label('Assignment')
                    ->description(fn ($record) => $record->assignment->course->course_code),
                
                TextColumn::make('submitted_at')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->label('Submitted')
                    ->description(fn ($record) => $record->submitted_at->diffForHumans()),
                
                TextColumn::make('is_late')
                    ->badge()
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => $state ? 'Late' : 'On Time')
                    ->colors([
                        'danger' => true,
                        'success' => false,
                    ]),
                
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'submitted',
                        'success' => 'graded',
                        'info' => 'returned',
                        'danger' => 'resubmit',
                    ]),
                
                TextColumn::make('grade.percentage')
                    ->label('Grade')
                    ->formatStateUsing(fn ($state) => $state ? $state . '%' : '-')
                    ->sortable()
                    ->color(fn ($state) => match(true) {
                        $state >= 90 => 'success',
                        $state >= 80 => 'info',
                        $state >= 70 => 'warning',
                        $state >= 60 => 'danger',
                        default => 'gray',
                    })
                    ->weight('bold'),
                
                TextColumn::make('grade.letter_grade')
                    ->label('Letter')
                    ->badge()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'submitted' => 'Submitted',
                        'graded' => 'Graded',
                        'returned' => 'Returned',
                        'resubmit' => 'Resubmit',
                    ]),
                
                TernaryFilter::make('is_late')
                    ->label('Late Submissions')
                    ->trueLabel('Late only')
                    ->falseLabel('On time only')
                    ->placeholder('All submissions'),
                
                SelectFilter::make('assignment')
                    ->relationship('assignment', 'title')
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('course')
                    ->label('Course')
                    ->relationship('assignment.course', 'title')
                    ->searchable()
                    ->preload(),
                
                // Filter::make('pending_grading')
                //     ->query(fn (Builder $query) => $query->where('status', 'submitted')->doesntHave('grade'))
                //     ->label('Pending Grading')
                //     ->default(),
                
                // Filter::make('graded_unpublished')
                //     ->query(fn (Builder $query) => $query->whereHas('grade', function ($q) {
                //         $q->where('is_published', false);
                //     }))
                //     ->label('Graded (Unpublished)'),
            ])
            ->recordActions([
                Action::make('grade')
                    ->icon('heroicon-o-pencil-square')
                    ->color('success')
                    ->label(fn (Submission $record) => $record->grade ? 'Edit Grade' : 'Grade')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('student_name')
                                    ->label('Student')
                                    ->state(fn (Submission $record) => $record->student->user->full_name),
                                
                                TextEntry::make('assignment_title')
                                    ->label('Assignment')
                                    ->state(fn (Submission $record) => $record->assignment->title),
                                
                                TextEntry::make('submission_status')
                                    ->label('Submission Status')
                                    ->state(fn (Submission $record) => $record->is_late ? '⚠️ Late Submission' : '✅ On Time'),
                                
                                TextEntry::make('max_points')
                                    ->label('Maximum Score')
                                    ->state(fn (Submission $record) => $record->assignment->max_score . ' points'),
                            ]),
                        
                        Section::make('Grading')
                            ->schema([
                                TextInput::make('score')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->maxValue(fn (Submission $record) => $record->assignment->max_score)
                                    ->suffix(fn (Submission $record) => '/ ' . $record->assignment->max_score)
                                    ->helperText(fn (Submission $record) => 'Enter score between 0 and ' . $record->assignment->max_score)
                                    ->default(fn (Submission $record) => $record->grade?->score),
                                
                                RichEditor::make('feedback')
                                    ->required()
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'underline',
                                        'bulletList',
                                        'orderedList',
                                    ])
                                    ->placeholder('Provide detailed feedback to help the student improve...')
                                    ->default(fn (Submission $record) => $record->grade?->feedback)
                                    ->columnSpanFull(),
                                
                                Toggle::make('publish_immediately')
                                    ->label('Publish grade immediately')
                                    ->default(fn (Submission $record) => $record->grade?->is_published ?? true)
                                    ->helperText('Students will be notified via email if published')
                                    ->inline(false),
                            ]),
                    ])
                    ->action(function (Submission $record, array $data) {
                        $grade = $record->grade()->updateOrCreate(
                            ['submission_id' => $record->id],
                            [
                                'instructor_id' => Auth::user()->instructor->id,
                                'score' => $data['score'],
                                'percentage' => null,
                                'max_score' => $record->assignment->max_score,
                                'feedback' => $data['feedback'],
                                'graded_at' => now(),
                                'is_published' => $data['publish_immediately'],
                                'published_at' => $data['publish_immediately'] ? now() : null,
                            ]
                        );

                        $grade->calculatePercentage();
                        $grade->calculateLetterGrade();

                        $record->update(['status' => 'graded']);

                        Notification::make()
                            ->success()
                            ->title('Submission graded successfully')
                            ->body($data['publish_immediately'] 
                                ? 'Student has been notified via email' 
                                : 'Grade saved as draft (not visible to student)')
                            ->send();
                    })
                    ->modalWidth('3xl')
                    ->slideOver(),
                
                Action::make('publish')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Publish Grade')
                    ->modalDescription('The student will be notified via email and can view their grade.')
                    ->action(function (Submission $record) {
                        $record->grade->update([
                            'is_published' => true,
                            'published_at' => now(),
                        ]);
                        
                        Notification::make()
                            ->success()
                            ->title('Grade published')
                            ->body('Student has been notified')
                            ->send();
                    })
                    ->visible(fn (Submission $record) => $record->grade && !$record->grade->is_published),
                
                ViewAction::make(),
                
                Action::make('download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn (Submission $record) => !empty($record->attachments) 
                        ? asset('storage/' . $record->attachments[0]) 
                        : null)
                    ->openUrlInNewTab()
                    ->visible(fn (Submission $record) => !empty($record->attachments)),
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
