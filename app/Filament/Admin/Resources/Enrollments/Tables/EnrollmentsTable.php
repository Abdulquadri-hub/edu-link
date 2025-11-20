<?php

namespace App\Filament\Admin\Resources\Enrollments\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Notifications\Notification as FilamentNotification;
use App\Notifications\EnrollmentApproved;
use App\Models\Enrollment;

class EnrollmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.student_id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('student.user.full_name')
                    ->label('Student Name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                TextColumn::make('course.course_code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('course.title')
                    ->label('Course')
                    ->searchable()
                    ->limit(30),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'active',
                        'info' => 'completed',
                        'warning' => 'dropped',
                        'danger' => 'failed',
                    ]),
                TextColumn::make('progress_percentage')
                    ->label('Progress'),
                TextColumn::make('final_grade')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('enrolled_at')
                    ->date()
                    ->sortable(),
                TextColumn::make('completed_at')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('notes.receipt')
                    ->label('Receipt')
                    ->url(fn ($record) => $record->notes['receipt'] ?? null)
                    ->openUrlInNewTab()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'dropped' => 'Dropped',
                        'failed' => 'Failed',
                    ]),
                SelectFilter::make('course')
                    ->relationship('course', 'title'),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('approve_payment')
                    ->label('Approve Payment')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Enrollment $record) {
                        if ($record->status !== 'pending_payment') {
                            throw new \Exception('Only pending payment enrollments can be approved.');
                        }
                        $record->update(['status' => 'active', 'enrolled_at' => now()]);
                        // notify student and parents
                        try {
                            $record->student->user?->notify(new EnrollmentApproved($record));
                            foreach ($record->student->parents as $parent) {
                                $parent->user?->notify(new EnrollmentApproved($record));
                            }
                        } catch (\Exception $e) {
                            // non-blocking
                        }
                        FilamentNotification::make()->success()->title('Enrollment Approved')->body('Enrollment status set to active.')->send();
                    })
                    ->visible(fn ($record) => $record->status === 'pending_payment'),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
