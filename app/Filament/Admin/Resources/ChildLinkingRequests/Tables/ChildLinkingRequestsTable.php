<?php

namespace App\Filament\Admin\Resources\ChildLinkingRequests\Tables;

use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class ChildLinkingRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('parent.user.full_name')
                    ->label('Parent')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable()
                    ->description(fn ($record) => $record->parent->user->email),
                
                TextColumn::make('student.student_id')
                    ->label('Student ID')
                    ->searchable()
                    ->copyable(),
                
                TextColumn::make('student.user.full_name')
                    ->label('Student')
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
                    ->falseIcon('heroicon-o-x-circle'),
                
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
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending Review',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                
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
                    ->schema([
                        Textarea::make('admin_notes')
                            ->label('Admin Notes (Optional)')
                            ->rows(3)
                            ->placeholder('Add any notes about this approval...'),
                    ])
                    ->action(function ($record, array $data) {
                        $success = $record->approve(
                            Auth::id(),
                            $data['admin_notes'] ?? null
                        );
                        
                        if ($success) {
                            Notification::make()
                                ->success()
                                ->title('Request Approved')
                                ->body('The parent has been successfully linked to the student.')
                                ->send();
                        } else {
                            Notification::make()
                                ->danger()
                                ->title('Approval Failed')
                                ->body('This parent is already linked to the student.')
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
                            ->rows(3)
                            ->placeholder('Explain why this request is being rejected...'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->reject(Auth::id(), $data['reason']);
                        
                        Notification::make()
                            ->success()
                            ->title('Request Rejected')
                            ->body('The linking request has been rejected.')
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status === 'pending'),
                
                ViewAction::make(),
            ])
            ->toolbarActions([])
            ->defaultSort('created_at', 'desc');
    }
}
