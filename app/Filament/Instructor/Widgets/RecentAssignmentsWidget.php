<?php

namespace App\Filament\Instructor\Widgets;

use App\Models\Assignment;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class RecentAssignmentsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
             ->query(
                Assignment::query()
                    ->where('instructor_id', Auth::user()->instructor->id)
                    ->where('status', 'published')
                    ->orderBy('due_at', 'desc')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->limit(40)
                    ->weight('bold')
                    ->description(fn ($record) => $record->course->course_code . ' - ' . $record->course->title),
                
                TextColumn::make('course.course_code')
                    ->label('Course')
                    ->badge()
                    ->color('primary'),
                
                TextColumn::make('type')
                    ->badge()
                    ->colors([
                        'primary' => 'quiz',
                        'success' => 'homework',
                        'info' => 'project',
                        'danger' => 'exam',
                        'warning' => 'other',
                    ])
                    ->icons([
                        'heroicon-o-clipboard-document-check' => 'quiz',
                        'heroicon-o-pencil-square' => 'homework',
                        'heroicon-o-briefcase' => 'project',
                        'heroicon-o-academic-cap' => 'exam',
                    ]),
                
                TextColumn::make('due_at')
                    ->label('Due Date')
                    ->dateTime('M d, Y')
                    ->description(fn ($record) => $record->due_at->isPast() 
                        ? 'Overdue' 
                        : $record->due_at->diffForHumans())
                    ->color(fn ($record) => $record->due_at->isPast() ? 'danger' : 'success')
                    ->sortable(),
                
                TextColumn::make('submissions_count')
                    ->counts('submissions')
                    ->label('Submissions')
                    ->badge()
                    ->color('info')
                    ->description(fn ($record) => $record->getGradedCount() . ' graded'),
                
                TextColumn::make('progress')
                    ->label('Progress')
                    ->getStateUsing(function ($record) {
                        $total = $record->course->activeEnrollments()->count();
                        if ($total === 0) return '0%';
                        
                        $submitted = $record->submissions()->count();
                        $percentage = round(($submitted / $total) * 100);
                        return $percentage . '%';
                    })
                    ->badge()
                    ->color(fn ($state) => match(true) {
                        intval($state) >= 80 => 'success',
                        intval($state) >= 50 => 'warning',
                        default => 'danger',
                    }),
                
                TextColumn::make('pending_grading')
                    ->label('Pending')
                    ->getStateUsing(fn ($record) => 
                        $record->submissions()->where('status', 'submitted')->doesntHave('grade')->count()
                    )
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'success')
                    ->icon(fn ($state) => $state > 0 ? 'heroicon-o-exclamation-circle' : 'heroicon-o-check-circle'),
            ])
            ->recordActions([
                Action::make('grade')
                    ->label('Grade')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->size('sm')
                    ->url(fn (Assignment $record) => route('filament.instructor.resources.submissions.index', [
                        'tableFilters' => [
                            'assignment' => ['value' => $record->id],
                            'pending_grading' => ['isActive' => true]
                        ]
                    ]))
                    ->badge(fn (Assignment $record) => 
                        $record->submissions()->where('status', 'submitted')->doesntHave('grade')->count()
                    )
                    ->visible(fn (Assignment $record) => 
                        $record->submissions()->where('status', 'submitted')->doesntHave('grade')->count() > 0
                    ),
                
                Action::make('view_submissions')
                    ->label('View All')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->size('sm')
                    ->url(fn (Assignment $record) => route('filament.instructor.resources.submissions.index', [
                        'tableFilters' => ['assignment' => ['value' => $record->id]]
                    ])),
                
                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->color('gray')
                    ->size('sm')
                    ->url(fn (Assignment $record) => route('filament.instructor.resources.assignments.edit', ['record' => $record->id])),
            ])
            ->emptyStateHeading('No Published Assignments')
            ->emptyStateDescription('You haven\'t published any assignments yet.')
            ->emptyStateIcon('heroicon-o-document-text')
            ->emptyStateActions([
                Action::make('create')
                    ->label('Create Assignment')
                    ->icon('heroicon-o-plus')
                    ->url(route('filament.instructor.resources.assignments.create'))
                    ->button(),
            ]);
    }

    protected function getTableHeading(): string
    {
        return 'Recent Assignments';
    }

    protected function getTableDescription(): ?string
    {
        return 'Your 5 most recent published assignments';
    }
}
