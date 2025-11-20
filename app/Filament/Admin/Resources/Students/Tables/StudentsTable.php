<?php

namespace App\Filament\Admin\Resources\Students\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use App\Models\AcademicLevel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\StudentPromotedToNextLevel;

class StudentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student_id')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('user.first_name')
                    ->label('First Name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('user.last_name')
                    ->label('Last Name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('user.email')
                    ->label('First Name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('academicLevel.name')
                    ->label('Grade Level')
                    ->badge()
                    ->color(fn ($record) => match($record->academicLevel?->level_type) {
                        'elementary' => 'success',
                        'middle' => 'warning',
                        'high' => 'danger',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),

                TextColumn::make('date_of_birth')
                    ->date()
                    ->sortable(),
                TextColumn::make('gender')
                    ->badge()
                    ->colors([
                        'primary' => 'male',
                        'success'  => 'female',
                        'warning' => 'other'
                    ]),
                TextColumn::make('enrollment_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('enrollment_status')
                    ->badge()
                    ->colors([
                        'success' => 'active',
                        'info' => 'graduated',
                        'warning' => 'dropped',
                        'danger' => 'suspended',
                    ]),
                TextColumn::make('enrollments_count')
                    ->counts('enrollments')
                    ->label('Courses')
                    ->sortable(),
                TextColumn::make('parents_count')
                    ->counts('parents')
                    ->label('Parents')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('academic_level_id')
                    ->label('Grade Level')
                    ->relationship('academicLevel', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('enrollment_status')
                    ->options([
                        'active' => 'Active',
                        'graduated' => 'Graduated',
                        'dropped' => 'Dropped',
                        'suspended' => 'Suspended',
                    ]),
                SelectFilter::make('gender'),
                // TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('promote')
                    ->label('Promote')
                    ->icon('heroicon-o-chevron-up')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Promote Student')
                    ->modalDescription(fn ($record) => 'Promote ' . $record->user->full_name . ' to the next grade level?')
                    ->modalSubmitActionLabel('Promote')
                    ->action(function ($record, $data) {
                        $actor = Auth::user();
                        $record->promoteToNextLevel($actor, $data['reason'] ?? null);
                    })
                    ->form([
                        Textarea::make('reason')->label('Reason (optional)'),
                    ])
                    ->visible(fn ($record) => AcademicLevel::where('grade_number', '>', $record->academicLevel?->grade_number ?? 0)->exists()),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
