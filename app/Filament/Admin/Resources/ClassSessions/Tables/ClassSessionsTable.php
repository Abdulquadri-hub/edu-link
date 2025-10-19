<?php

namespace App\Filament\Admin\Resources\ClassSessions\Tables;

use Filament\Tables\Table;
use App\Models\ClassSession;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class ClassSessionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                TextColumn::make('course.course_code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('instructor.user.full_name')
                    ->label('Instructor')
                    ->searchable(['first_name', 'last_name']),
                TextColumn::make('scheduled_at')
                    ->dateTime()
                    ->sortable(),
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
                    ->toggleable(),
                IconColumn::make('google_meet_link')
                    ->boolean()
                    ->label('Has Meet Link')
                    ->toggleable(),
                TextColumn::make('attendances_count')
                    ->counts('attendances')
                    ->label('Attendance')
                    ->sortable(),
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
                SelectFilter::make('instructor')
                    ->relationship('instructor.user', 'first_name')
            ])
            ->recordActions([
                Action::make('start')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (ClassSession $record) => $record->startSession())
                    ->visible(fn (ClassSession $record) => $record->status === 'scheduled'),
                Action::make('end')
                    ->icon('heroicon-o-stop')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (ClassSession $record) => $record->endSession())
                    ->visible(fn (ClassSession $record) => $record->status === 'in-progress'),
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
