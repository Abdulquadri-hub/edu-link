<?php

namespace App\Filament\Admin\Resources\StudentPromotions\Tables;

use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class StudentPromotionsTable
{
   public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('promotion_code')
                    ->label('Code')
                    ->searchable()
                    ->copyable()
                    ->weight('bold')
                    ->color('primary'),
                
                TextColumn::make('student.student_id')
                    ->label('Student ID')
                    ->searchable()
                    ->copyable(),
                
                TextColumn::make('student.user.full_name')
                    ->label('Student')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                
                TextColumn::make('fromLevel.name')
                    ->label('From')
                    ->badge()
                    ->color('gray')
                    ->placeholder('None'),
                
                TextColumn::make('toLevel.name')
                    ->label('To')
                    ->badge()
                    ->color('success'),
                
                TextColumn::make('promotion_type')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'regular' => 'Regular',
                        'skip' => 'Skip Grade',
                        'repeat' => 'Repeat',
                        'transfer' => 'Transfer',
                        'manual' => 'Manual',
                    }),
                
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'approved',
                        'success' => 'completed',
                        'danger' => 'rejected',
                    ])
                    ->sortable(),
                
                TextColumn::make('promotion_date')
                    ->label('Date')
                    ->date('M d, Y')
                    ->sortable(),
                
                TextColumn::make('promoter.full_name')
                    ->label('Promoted By')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'completed' => 'Completed',
                        'rejected' => 'Rejected',
                    ]),
                
                SelectFilter::make('promotion_type')
                    ->options([
                        'regular' => 'Regular',
                        'skip' => 'Skip Grade',
                        'repeat' => 'Repeat',
                        'transfer' => 'Transfer',
                        'manual' => 'Manual',
                    ]),
                
                SelectFilter::make('to_level')
                    ->label('Promoted To')
                    ->relationship('toLevel', 'name'),
                
                Filter::make('pending_only')
                    ->label('Pending Only')
                    ->query(fn (Builder $query) => $query->where('status', 'pending'))
                    ->default(),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Promotion') 
                    ->modalDescription(fn ($record) => "Approve promotion of {$record->student->user->full_name} from " . ($record->fromLevel->name ?? 'None') . " to {$record->toLevel->name}?")
                    ->action(function ($record) {
                        $success = $record->approve(Auth::id());
                        
                        if ($success) {
                            Notification::make()
                                ->success()
                                ->title('Promotion Approved')
                                ->body('Student has been promoted successfully.')
                                ->send();
                        }
                    })
                    ->visible(fn ($record) => $record->status === 'pending'),
                
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->schema([
                        Textarea::make('reason')
                            ->label('Reason for Rejection')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        $record->reject(Auth::id(), $data['reason']);
                        
                        Notification::make()
                            ->success()
                            ->title('Promotion Rejected')
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status === 'pending'),
                
                ViewAction::make(),
                EditAction::make()->visible(fn ($record) => $record->status === 'pending'),
                DeleteAction::make()->visible(fn ($record) => $record->status === 'pending'),
            ])
            ->toolbarActions([])
            ->defaultSort('promotion_date', 'desc');
    }
}
