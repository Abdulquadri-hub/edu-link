<?php

namespace App\Filament\Parent\Resources\UpcomingClassResouces\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Filters\Filter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class UpcomingClassResoucesTable
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
                
                TextColumn::make('course.title')
                    ->label('Course')
                    ->searchable()
                    ->limit(30),
                
                TextColumn::make('instructor.user.full_name')
                    ->label('Instructor')
                    ->searchable(['first_name', 'last_name']),
                
                TextColumn::make('scheduled_at')
                    ->label('Date & Time')
                    ->dateTime('M d, Y - H:i')
                    ->sortable()
                    ->description(fn ($record) => $record->scheduled_at->diffForHumans())
                    ->color('success')
                    ->weight('bold'),
                
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'info' => 'scheduled',
                        'warning' => 'in-progress',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ]),
                
                IconColumn::make('google_meet_link')
                    ->label('Meet Link')
                    ->boolean()
                    ->trueIcon('heroicon-o-video-camera')
                    ->falseIcon('heroicon-o-x-circle'),
            ])
            ->filters([
                SelectFilter::make('course')
                    ->relationship('course', 'title')
                    ->searchable()
                    ->preload(),
                
                Filter::make('today')
                    ->label('Today Only')
                    ->query(fn (Builder $query) => $query->whereDate('scheduled_at', today()))
                    ->toggle(),
                
                Filter::make('this_week')
                    ->label('This Week')
                    ->query(fn (Builder $query) => $query->whereBetween('scheduled_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ]))
                    ->default(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([])
            ->defaultSort('scheduled_at', 'asc');
    }
}
