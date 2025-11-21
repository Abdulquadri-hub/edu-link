<?php

namespace App\Filament\Student\Resources\MyEnrollmentRequests\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\ForceDeleteBulkAction;

class MyEnrollmentRequestsTable
{
    public static function configure(Table $table): Table
    {
                return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('request_code')
                    ->label('Request Code')
                    ->searchable()
                    ->copyable()
                    ->weight('bold')
                    ->color('primary'),
                
                \Filament\Tables\Columns\TextColumn::make('course.title')
                    ->label('Course')
                    ->searchable()
                    ->limit(40)
                    ->description(fn ($record) => $record->course->course_code),
                
                \Filament\Tables\Columns\TextColumn::make('frequency_preference')
                    ->label('Frequency')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn ($record) => $record->frequencyText),
                
                \Filament\Tables\Columns\TextColumn::make('quoted_price')
                    ->label('Price')
                    ->money(fn ($record) => $record->currency)
                    ->weight('bold')
                    ->color('success'),
                
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($record) => $record->statusColor)
                    ->formatStateUsing(fn ($record) => $record->statusText)
                    ->sortable(),
                
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Requested')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->description(fn ($record) => $record->created_at->diffForHumans()),
                
                \Filament\Tables\Columns\TextColumn::make('processed_at')
                    ->label('Processed')
                    ->dateTime('M d, Y')
                    ->placeholder('Pending')
                    ->toggleable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending Review',
                        'parent_notified' => 'Parent Notified',
                        'payment_pending' => 'Awaiting Payment',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'cancelled' => 'Cancelled',
                    ]),
                
                \Filament\Tables\Filters\Filter::make('active')
                    ->label('Active Requests')
                    ->query(fn (Builder $query) => $query->whereIn('status', ['pending', 'parent_notified', 'payment_pending']))
                    ->default(),
            ])
            ->recordActions([
                \Filament\Actions\Action::make('cancel')
                    ->label('Cancel Request')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Cancel Enrollment Request')
                    ->modalDescription('Are you sure you want to cancel this enrollment request?')
                    ->action(function ($record) {
                        $record->cancel();
                        
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Request Cancelled')
                            ->body('Your enrollment request has been cancelled.')
                            ->send();
                    })
                    ->visible(fn ($record) => $record->canBeCancelled()),
                
                \Filament\Actions\ViewAction::make(),
            ])
            ->toolbarActions([])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No Enrollment Requests')
            ->emptyStateDescription('Browse available courses to request enrollment')
            ->emptyStateIcon('heroicon-o-clipboard-document-list');
    }
}
