<?php

namespace App\Filament\Student\Resources\Assignments\Tables;

use App\Models\Assignment;
use App\Models\Submission;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Infolists\Components\TextEntry;

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
                    ->wrap()
                    ->description(fn ($record) => $record->course->course_code),
                
                TextColumn::make('type')
                    ->badge()
                    ->colors([
                        'primary' => 'quiz',
                        'success' => 'homework',
                        'info' => 'project',
                        'danger' => 'exam',
                    ]),
                
                TextColumn::make('due_at')
                    ->label('Due Date')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->description(fn ($record) => $record->due_at->isPast() ? ' Overdue' : $record->due_at->diffForHumans())
                    ->color(fn ($record) => $record->due_at->isPast() ? 'danger' : 'success'),
                
                TextColumn::make('max_score')
                    ->label('Points')
                    ->suffix(' pts')
                    ->sortable(),
                
                TextColumn::make('submission_status')
                    ->label('My Status')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        $submission = $record->submissions->first();
                        return $submission ? $submission->status : 'Not Submitted';
                    })
                    ->colors([
                        'danger' => 'Not Submitted',
                        'warning' => 'submitted',
                        'success' => 'graded',
                        'info' => 'returned',
                    ]),
                
                TextColumn::make('grade')
                    ->label('Grade')
                    ->getStateUsing(function ($record) {
                        $submission = $record->submissions->first();
                        if (!$submission || !$submission->grade || !$submission->grade->is_published) {
                            return '-';
                        }
                        return $submission->grade->percentage . '%';
                    })
                    ->color(fn ($state) => $state === '-' ? 'gray' : 'success')
                    ->weight('bold'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'quiz' => 'Quiz',
                        'homework' => 'Homework',
                        'project' => 'Project',
                        'exam' => 'Exam',
                    ]),
                
                SelectFilter::make('course')
                    ->relationship('course', 'title')
                    ->searchable()
                    ->preload(),
                
                Filter::make('pending')
                    ->label('Not Submitted')
                    ->query(function (Builder $query) {
                        $student = Auth::user()->student;
                        $query->whereDoesntHave('submissions', function ($q) use ($student) {
                            $q->where('student_id', $student->id);
                        });
                    })
                    ->default(),
                
                Filter::make('overdue')
                    ->label('Overdue')
                    ->query(fn (Builder $query) => $query->where('due_at', '<', now())),
            ])
            ->recordActions([
                Action::make('submit')
                    ->label('Submit')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->schema([
                        TextEntry::make('assignment_info')
                            ->label('Assignment')
                            ->state(fn ($record) => $record->title . ' (Max: ' . $record->max_score . ' points)'),
                        
                        TextEntry::make('due_date_info')
                            ->label('Due Date')
                            ->state(fn ($record) => $record->due_at->format('M d, Y H:i') . 
                                ($record->due_at->isPast() ? ' ⚠️ OVERDUE' : ' (' . $record->due_at->diffForHumans() . ')'))
                            ->columnSpanFull(),
                        
                        RichEditor::make('content')
                            ->label('Your Answer/Solution')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'link',
                            ])
                            ->placeholder('Write your answer here...')
                            ->columnSpanFull(),
                        
                        FileUpload::make('attachments')
                            ->label('Upload Files')
                            ->multiple()
                            ->directory('student-submissions')
                            ->maxFiles(5)
                            ->maxSize(10240)
                            ->helperText('Max 5 files, 10MB each')
                            ->columnSpanFull(),
                    ])
                    ->action(function (Assignment $record, array $data) {
                        $student = Auth::user()->student;
                        
                        // Check if already submitted
                        $existing = $existing = Submission::where('assignment_id', $record->id)
                                        ->where('student_id', $student->id)
                                        ->first(); ;
                        
                        if ($existing) {
                            Notification::make()
                                ->warning()
                                ->title('Already Submitted')
                                ->body('You have already submitted this assignment')
                                ->send();
                            return;
                        }
                        
                        //Submit via repository
                        Submission::create([
                            'assignment_id' => $record->id,
                            'student_id' => $student->id,
                            'content' => $data['content'] ?? null,
                            'attachments' => $data['attachments'] ?? null,
                            'submitted_at' => now(),
                            'status' => 'submitted',
                            'attempt_number' => 1,
                        ]);
                        
                        Notification::make()
                            ->success()
                            ->title('Assignment Submitted')
                            ->body('Your submission has been received. The instructor will grade it soon.')
                            ->send();
                    })
                    ->visible(fn ($record) => $record->submissions->isEmpty() && 
                        ($record->allows_late_submission || !$record->due_at->isPast()))
                    ->modalWidth('3xl')
                    ->slideOver(),
                
                ViewAction::make(),
                
                Action::make('viewGrade')
                    ->label('View Grade')
                    ->icon('heroicon-o-academic-cap')
                    ->color('info')
                    ->url(fn ($record) => route('filament.student.resources.grades.index', [
                        'tableFilters' => ['assignment' => ['value' => $record->id]]
                    ]))
                    ->visible(fn ($record) => $record->submissions->first()?->grade?->is_published),
            ])
            ->toolbarActions([])
            ->defaultSort('due_at', 'asc');
    }
}
