<?php

namespace App\Filament\Parent\Resources\ChildGrades\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Filters\Filter;
use Filament\Support\Enums\TextSize;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class ChildGradesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('submission.student.user.full_name')
                    ->label('Child')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                
                TextColumn::make('submission.assignment.title')
                    ->label('Assignment')
                    ->searchable()
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
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'A' => 'success',
                        'B' => 'info',
                        'C' => 'warning',
                        default => 'danger',
                    }),
                
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
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->description(fn ($record) => $record->published_at->diffForHumans()),
            ])
            ->filters([
                SelectFilter::make('student')
                    ->label('Child')
                    ->relationship('submission.student', 'student_id', function ($query) {
                        $parent = Auth::user()->parent;
                        $query->whereHas('parents', function ($q) use ($parent) {
                            $q->where('student_parent.parent_id', $parent->id);
                        });
                    })
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('course')
                    ->label('Course')
                    ->relationship('submission.assignment.course', 'title')
                    ->searchable()
                    ->preload(),
                
                Filter::make('low_grades')
                    ->label('Below 70%')
                    ->query(fn (Builder $query) => $query->where('percentage', '<', 70))
                    ->toggle(),
                
                Filter::make('recent')
                    ->label('This Week')
                    ->query(fn (Builder $query) => $query->where('published_at', '>', now()->subWeek()))
                    ->default(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
            // ->defaultSort('classSession.scheduled_at', 'desc');
    }
}
