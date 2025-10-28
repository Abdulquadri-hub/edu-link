<?php

namespace App\Filament\Student\Resources\ClassSessions\Tables;

use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Filters\Filter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
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
                    ->limit(40)
                    ->wrap()
                    ->description(fn ($record) => $record->course->course_code),
                
                TextColumn::make('instructor.user.full_name')
                    ->label('Instructor')
                    ->searchable(['first_name', 'last_name']),
                
                TextColumn::make('scheduled_at')
                    ->label('Date & Time')
                    ->dateTime('M d, Y - H:i')
                    ->sortable()
                    ->description(fn ($record) => $record->scheduled_at->isPast() 
                        ? ($record->status === 'completed' ? 'Completed' : 'Missed') 
                        : $record->scheduled_at->diffForHumans())
                    ->color(fn ($record) => $record->scheduled_at->isPast() && $record->status !== 'completed' 
                        ? 'danger' 
                        : 'success'),
                
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'info' => 'scheduled',
                        'warning' => 'in-progress',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ]),
                
                IconColumn::make('google_meet_link')
                    ->label('Has Link')
                    ->boolean()
                    ->trueIcon('heroicon-o-video-camera')
                    ->falseIcon('heroicon-o-x-circle'),
                
                TextColumn::make('duration_minutes')
                    ->label('Duration')
                    ->suffix(' min')
                    ->toggleable()
                    ->placeholder('N/A'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'in-progress' => 'In Progress',
                        'completed' => 'Completed',
                    ]),
                
                SelectFilter::make('course')
                    ->relationship('course', 'title')
                    ->searchable()
                    ->preload(),
                
                Filter::make('upcoming')
                    ->label('Upcoming Only')
                    ->query(fn (Builder $query) => $query->where('scheduled_at', '>', now()))
                    ->default(),
            ])
            ->recordActions([
                Action::make('join')
                    ->label('Join Class')
                    ->icon('heroicon-o-video-camera')
                    ->color('success')
                    ->url(fn ($record) => $record->google_meet_link)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => !empty($record->google_meet_link) && 
                        in_array($record->status, ['scheduled', 'in-progress'])),
                
                ViewAction::make(),
            ])
            ->toolbarActions([])
            ->defaultSort('scheduled_at', 'asc');
    }
}
