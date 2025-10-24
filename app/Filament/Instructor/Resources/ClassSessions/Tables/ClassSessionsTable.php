<?php

namespace App\Filament\Instructor\Resources\ClassSessions\Tables;

use Filament\Tables\Table;
use App\Models\ClassSession;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Filters\Filter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class ClassSessionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                 TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->wrap(),
                TextColumn::make('course.course_code')
                    ->searchable()
                    ->sortable()
                    ->label('Course'),
                TextColumn::make('scheduled_at')
                    ->dateTime('M d, Y - H:i')
                    ->sortable()
                    ->description(fn ($record) => $record->scheduled_at->diffForHumans()),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'info' => 'scheduled',
                        'warning' => 'in-progress',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ]),
                TextColumn::make('duration_minutes')
                    ->suffix(' min')
                    ->sortable()
                    ->placeholder('N/A'),
                TextColumn::make('attendances_count')
                    ->counts('attendances')
                    ->label('Attendance')
                    ->sortable(),
                IconColumn::make('google_meet_link')
                    ->boolean()
                    ->label('Meet Link')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'in-progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
                SelectFilter::make('course')
                    ->relationship('course', 'title'),
                Filter::make('upcoming')
                    ->query(fn (Builder $query) => $query->where('scheduled_at', '>', now()))
                    ->label('Upcoming Only'),
            ])
            ->recordActions([
                    Action::make('start')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Start Class Session')
                    ->modalDescription('This will mark the session as in-progress and record the start time.')
                    ->action(function (ClassSession $record) {
                        $record->startSession();
                        Notification::make()
                            ->success()
                            ->title('Class started')
                            ->body('Students can now join.')
                            ->send();
                    })
                    ->visible(fn (ClassSession $record) => $record->status === 'scheduled'),
                
                Action::make('end')
                    ->icon('heroicon-o-stop')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('End Class Session')
                    ->modalDescription('This will mark the session as completed and calculate the duration.')
                    ->action(function (ClassSession $record) {
                        $record->endSession();
                        Notification::make()
                            ->success()
                            ->title('Class ended')
                            ->body("Duration: {$record->duration_minutes} minutes")
                            ->send();
                    })
                    ->visible(fn (ClassSession $record) => $record->status === 'in-progress'),
                
                Action::make('join')
                    ->icon('heroicon-o-video-camera')
                    ->color('info')
                    ->url(fn (ClassSession $record) => $record->google_meet_link)
                    ->openUrlInNewTab()
                    ->visible(fn (ClassSession $record) => !empty($record->google_meet_link)),
                
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
