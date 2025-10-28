<?php

namespace App\Filament\Parent\Resources\Children\Tables;

use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;

class ChildrenTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
               ImageColumn::make('user.avatar')
                    ->label('Photo')
                    ->circular()
                    ->defaultImageUrl(asset('images/default-avatar.png')),
                
               TextColumn::make('student_id')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                
               TextColumn::make('user.full_name')
                    ->label('Name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable()
                    ->description(fn ($record) => $record->user->email),
                
               TextColumn::make('active_enrollments_count')
                    ->counts('activeEnrollments')
                    ->label('Courses')
                    ->badge()
                    ->color('info'),
                
               TextColumn::make('overall_progress')
                    ->label('Progress')
                    ->getStateUsing(fn ($record) => round($record->calculateOverallProgress(), 1) . '%')
                    ->color('success')
                    ->weight('bold'),
                
               TextColumn::make('attendance_rate')
                    ->label('Attendance')
                    ->getStateUsing(fn ($record) => round($record->calculateAttendanceRate(), 1) . '%')
                    ->color(fn ($state) => floatval($state) >= 85 ? 'success' : 'warning'),
                
               TextColumn::make('average_grade')
                    ->label('Avg Grade')
                    ->getStateUsing(function ($record) {
                        $grades = $record->grades()->where('is_published', true)->avg('percentage');
                        return $grades ? round($grades, 1) . '%' : 'N/A';
                    })
                    ->color(fn ($state) => match(true) {
                        $state === 'N/A' => 'gray',
                        floatval($state) >= 80 => 'success',
                        floatval($state) >= 70 => 'info',
                        floatval($state) >= 60 => 'warning',
                        default => 'danger',
                    })
                    ->weight('bold'),
                
               TextColumn::make('enrollment_status')
                    ->badge()
                    ->label('Status')
                    ->colors([
                        'success' => 'active',
                        'info' => 'graduated',
                        'warning' => 'dropped',
                        'danger' => 'suspended',
                    ]),
            ])
            ->filters([
                SelectFilter::make('enrollment_status')
                    ->options([
                        'active' => 'Active',
                        'graduated' => 'Graduated',
                        'dropped' => 'Dropped',
                        'suspended' => 'Suspended',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                
                Action::make('viewProgress')
                    ->label('View Progress')
                    ->icon('heroicon-o-chart-bar')
                    ->color('info')
                    ->url(fn ($record) => route('filament.parent.pages.child-progress', ['child' => $record->id])),
            ])
            ->toolbarActions([]);
    }
}
