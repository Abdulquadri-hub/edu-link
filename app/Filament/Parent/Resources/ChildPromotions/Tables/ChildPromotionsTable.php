<?php

namespace App\Filament\Parent\Resources\ChildPromotions\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class ChildPromotionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.user.full_name')
                    ->label('Child')
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
                    ->sortable()
                    ->description(fn ($record) => $record->promotion_date->diffForHumans()),
                
                TextColumn::make('effective_date')
                    ->label('Effective')
                    ->date('M d, Y')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('student')
                    ->label('Child')
                    ->relationship('student', 'student_id', function ($query) {
                        $parent = Auth::user()->parent;
                        $query->whereHas('parents', function ($q) use ($parent) {
                            $q->where('student_parent.parent_id', $parent->id);
                        });
                    })
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'completed' => 'Completed',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([])
            ->defaultSort('promotion_date', 'desc')
            ->emptyStateHeading('No Promotions Yet')
            ->emptyStateDescription('Your child\'s grade promotions will appear here')
            ->emptyStateIcon('heroicon-o-academic-cap');
    }
}
