<?php

namespace App\Filament\Admin\Resources\Parents\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;

class ParentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('parent_id')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('user.full_name')
                    ->label('Name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('user.phone')
                    ->label('Phone')
                    ->searchable(),
                TextColumn::make('occupation')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('children_count')
                    ->counts('children')
                    ->label('Children')
                    ->sortable(),
                TextColumn::make('preferred_contact_method')
                    ->badge()
                    ->colors([
                        'primary' => 'email',
                        'success' => 'phone',
                        'warning' => 'sms',
                    ]),
                IconColumn::make('receives_weekly_report')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('preferred_contact_method')
                    ->options([
                        'email' => 'Email',
                        'phone' => 'Phone',
                        'sms' => 'SMS',
                    ]),
                TernaryFilter::make('receives_weekly_report')
                    ->label('Receives Weekly Reports'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
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
