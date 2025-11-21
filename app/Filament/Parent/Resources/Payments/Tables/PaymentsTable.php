<?php

namespace App\Filament\Parent\Resources\Payments\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Filters\Filter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\ForceDeleteBulkAction;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('payment_reference')
                    ->label('Reference')
                    ->searchable()
                    ->copyable()
                    ->weight('bold')
                    ->tooltip('Click to copy'),
                
                TextColumn::make('student.user.full_name')
                    ->label('Child')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                
                TextColumn::make('course.title')
                    ->label('Course')
                    ->searchable()
                    ->limit(30)
                    ->description(fn ($record) => $record->course?->course_code),
                
                TextColumn::make('amount')
                    ->label('Amount')
                    ->money(fn ($record) => $record->currency)
                    ->sortable()
                    ->weight('bold'),
                
                TextColumn::make('payment_date')
                    ->label('Payment Date')
                    ->date('M d, Y')
                    ->sortable()
                    ->description(fn ($record) => $record->payment_date->diffForHumans()),
                
                TextColumn::make('payment_method')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state))),
                
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'verified',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->sortable(),
                
                IconColumn::make('subscription')
                    ->label('Subscription')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->getStateUsing(fn ($record) => $record->hasSubscription())
                    ->tooltip(fn ($record) => $record->hasSubscription() ? 'Subscription created' : 'No subscription yet'),
                
                TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable()
                    ->description(fn ($record) => $record->created_at->diffForHumans()),
            ])
            ->filters([
                SelectFilter::make('student')
                    ->label('Child')
                    ->relationship('student', 'student_id')
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('course')
                    ->label('Course')
                    ->relationship('course', 'title')
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending Verification',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                    ]),
                
                Filter::make('pending_only')
                    ->label('Pending Only')
                    ->query(fn (Builder $query) => $query->where('status', 'pending'))
                    ->default(),
            ])
            ->recordActions([
                ViewAction::make(),
                
                DeleteAction::make()
                    ->label('Delete')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Delete Payment Receipt')
                    ->modalDescription('Are you sure you want to delete this payment receipt?')
                    ->successNotificationTitle('Payment receipt deleted'),
            ])
            ->toolbarActions([])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No Payments Yet')
            ->emptyStateDescription('Upload a payment receipt to get started')
            ->emptyStateIcon('heroicon-o-credit-card');
    }
}
