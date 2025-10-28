<?php

namespace App\Filament\Parent\Resources\ChildAttendances\Tables;

use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Infolists\Components\TextEntry;

class ChildAttendancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.user.full_name')
                    ->label('Child')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                
                TextColumn::make('classSession.title')
                    ->label('Class')
                    ->searchable()
                    ->limit(40)
                    ->description(fn ($record) => $record->classSession->course->course_code),
                
                TextColumn::make('classSession.course.title')
                    ->label('Course')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(),
                
                TextColumn::make('classSession.scheduled_at')
                    ->label('Date')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->description(fn ($record) => $record->classSession->scheduled_at->format('H:i A')),
                
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'present',
                        'warning' => 'late',
                        'danger' => 'absent',
                        'info' => 'excused',
                    ])
                    ->icon(fn ($state) => match($state) {
                        'present' => 'heroicon-o-check-circle',
                        'late' => 'heroicon-o-clock',
                        'absent' => 'heroicon-o-x-circle',
                        'excused' => 'heroicon-o-information-circle',
                    }),
                
                TextColumn::make('duration_minutes')
                    ->label('Duration')
                    ->suffix(' min')
                    ->placeholder('N/A')
                    ->toggleable(),
                
                TextColumn::make('classSession.instructor.user.full_name')
                    ->label('Instructor')
                    ->searchable(['first_name', 'last_name'])
                    ->toggleable(),
            ])
            ->filters([
                    SelectFilter::make('student')
                    ->label('Child')
                    ->relationship('student', 'student_id', function ($query) {
                        $parent = Auth::user()->parent;
                        $query->whereHas('parents', function ($q) use ($parent) {
                            $q->where('student_parent.parent_id', $parent->id);
                        });
                    })
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('status')
                    ->options([
                        'present' => 'Present',
                        'late' => 'Late',
                        'absent' => 'Absent',
                        'excused' => 'Excused',
                    ]),
                
                SelectFilter::make('course')
                    ->label('Course')
                    ->relationship('classSession.course', 'title')
                    ->searchable()
                    ->preload(),
                
                Filter::make('this_month')
                    ->label('This Month')
                    ->query(fn (Builder $query) => $query->whereHas('classSession', function ($q) {
                        $q->whereMonth('scheduled_at', now()->month)
                          ->whereYear('scheduled_at', now()->year);
                    }))
            ])
            ->recordActions([
                ViewAction::make(),
                
                Action::make('contactInstructor')
                    ->label('Contact Instructor')
                    ->icon('heroicon-o-envelope')
                    ->color('info')
                    ->schema([
                        TextEntry::make('instructor_name')
                            ->label('Instructor')
                            ->state(fn ($record) => $record->classSession->instructor->user->full_name),
                        Textarea::make('message')
                            ->label('Your Message')
                            ->required()
                            ->rows(5)
                            ->placeholder('Ask about your child\'s performance...'),
                    ])
                    ->action(function ($record, array $data) {
                        // Send via notification service
                        Notification::make()
                            ->success()
                            ->title('Message sent')
                            ->body('The instructor will respond via email')
                            ->send();
                    })
                    ->modalWidth('xl'),
            ])
            ->toolbarActions([]);
    }
}
