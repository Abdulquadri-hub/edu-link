<?php

namespace App\Filament\Parent\Resources\LinkChildren\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Filters\Filter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class LinkChildrenTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.student_id')
                    ->label('Student ID')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                
                TextColumn::make('student.user.full_name')
                    ->label('Student Name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable()
                    ->description(fn ($record) => $record->student->user->email),
                
                TextColumn::make('relationship')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                
                IconColumn::make('is_primary_contact')
                    ->label('Primary')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
                
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->sortable(),
                
                TextColumn::make('created_at')
                    ->label('Requested')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->description(fn ($record) => $record->created_at->diffForHumans()),
                
                TextColumn::make('reviewed_at')
                    ->label('Reviewed')
                    ->dateTime('M d, Y')
                    ->placeholder('Not yet reviewed')
                    ->description(fn ($record) => $record->reviewed_at?->diffForHumans())
                    ->toggleable(),
                
                TextColumn::make('reviewer.full_name')
                    ->label('Reviewed By')
                    ->placeholder('Pending')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending Review',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                
                Filter::make('pending_only')
                    ->label('Show Pending Only')
                    ->query(fn (Builder $query) => $query->where('status', 'pending'))
                    ->default(),
            ])
            ->recordActions([
                ViewAction::make(),
                
                DeleteAction::make()
                    ->label('Cancel Request')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Cancel Link Request')
                    ->modalDescription('Are you sure you want to cancel this linking request?')
                    ->successNotificationTitle('Request cancelled'),
            ])
            ->toolbarActions([])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No Linking Requests')
            ->emptyStateDescription('Click the button above to request linking with a child')
            ->emptyStateIcon('heroicon-o-user-plus');
    }
}
