<?php

namespace App\Filament\Instructor\Widgets;

use Filament\Tables\Table;
use App\Models\ClassSession;
use Filament\Actions\Action;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class UpcomingClassesWidget extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
               ->query(
                ClassSession::query()
                    ->where('instructor_id', Auth::user()->instructor->id)
                    ->where('scheduled_at', '>', now())
                    ->where('status', 'scheduled')
                    ->orderBy('scheduled_at')
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
                    ->color('info'),
                
                TextColumn::make('scheduled_at')
                    ->label('Scheduled')
                    ->dateTime('M d, Y')
                    ->description(fn ($record) => $record->scheduled_at->format('h:i A'))
                    ->sortable(),
                
                TextColumn::make('time_until')
                    ->label('Starts In')
                    ->getStateUsing(fn ($record) => $record->scheduled_at->diffForHumans())
                    ->badge()
                    ->color(fn ($record) => match(true) {
                        $record->scheduled_at->isToday() => 'warning',
                        $record->scheduled_at->isTomorrow() => 'info',
                        default => 'gray',
                    }),
                
                IconColumn::make('google_meet_link')
                    ->label('Meet Link')
                    ->boolean()
                    ->trueIcon('heroicon-o-video-camera')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                
                TextColumn::make('students_count')
                    ->label('Expected')
                    ->getStateUsing(fn ($record) => $record->course->activeEnrollments()->count())
                    ->suffix(' students')
                    ->icon('heroicon-o-user-group'),
            ])
            ->recordActions([
                Action::make('start')
                    ->label('Start Class')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->size('sm')
                    ->requiresConfirmation()
                    ->modalHeading('Start Class Session')
                    ->modalDescription(fn ($record) => 'Start "' . $record->title . '" now?')
                    ->modalSubmitActionLabel('Start Class')
                    ->action(function (ClassSession $record) {
                        $record->startSession();
                        Notification::make()
                            ->success()
                            ->title('Class started!')
                            ->body('Students can now join the session.')
                            ->send();
                    })
                    ->visible(fn (ClassSession $record) => $record->scheduled_at->isPast() || $record->scheduled_at->diffInMinutes(now()) <= 15),
                
                Action::make('join')
                    ->label('Join')
                    ->icon('heroicon-o-video-camera')
                    ->color('info')
                    ->size('sm')
                    ->url(fn (ClassSession $record) => $record->google_meet_link)
                    ->openUrlInNewTab()
                    ->visible(fn (ClassSession $record) => !empty($record->google_meet_link)),
                
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->size('sm')
                    ->url(fn (ClassSession $record) => route('filament.instructor.resources.class-sessions.edit', ['record' => $record->id])),
            ])
            ->emptyStateHeading('No Upcoming Classes')
            ->emptyStateDescription('You don\'t have any scheduled classes in the near future.')
            ->emptyStateIcon('heroicon-o-calendar-days')
            ->emptyStateActions([
                Action::make('schedule')
                    ->label('Schedule a Class')
                    ->icon('heroicon-o-plus')
                    ->url(route('filament.instructor.resources.class-sessions.create'))
                    ->button(),
            ]);
    }

    protected function getTableHeading(): string
    {
        return 'Upcoming Classes';
    }

    protected function getTableDescription(): ?string
    {
        return 'Your next 5 scheduled class sessions';
    }
}
