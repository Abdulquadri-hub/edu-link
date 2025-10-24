<?php

namespace App\Filament\Admin\Resources\Students\RelationManagers;

use Filament\Tables\Table;
use App\Models\ParentModel;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DetachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Resources\RelationManagers\RelationManager;

class ParentsRelationManager extends RelationManager
{
    protected static string $relationship = 'parents';
    protected static ?string $recordTitleAttribute = 'parent_id';
    protected static ?string $inverseRelationship = 'children';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('parent_id')
            ->columns([
                TextColumn::make('parent_id')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('user.full_name')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('user.email')
                    ->searchable()
                    ->copyable(),
                
                TextColumn::make('pivot.relationship')->label('Relationship'),
                
                IconColumn::make('pivot.is_primary_contact')
                    ->boolean()
                    ->label('Primary'),
                
                IconColumn::make('pivot.can_view_grades')
                    ->boolean()
                    ->label('View Grades'),
                
                IconColumn::make('pivot.can_view_attendance')
                    ->boolean()
                    ->label('View Attendance'),
            ])
            ->filters([
                SelectFilter::make('relationship')
                    ->options([
                        'father' => 'Father',
                        'mother' => 'Mother',
                        'guardian' => 'Guardian',
                        'other' => 'Other',
                    ]),
            ])
            ->headerActions([
                AttachAction::make()
                   ->Label('Link Parent')
                   ->color('success')
                   ->icon("heroicon-o-link")
                   ->recordSelect(
                      fn (Select $select) => $select
                        ->placeholder('Enter parent first/last name')
                        ->searchable()
                        ->getSearchResultsUsing( fn  (string $search) => 
                            ParentModel::whereHas('user', fn ($q) => 
                                $q->where('user_type', 'parent')
                                    ->where(function ($query) use ($search) {
                                     $query->where('first_name', 'like', "%$search%")
                                        ->orWhere('last_name', 'like', "%$search%");
                                    })      
                            )
                            ->limit(10)
                            ->get()
                            ->mapWithKeys( fn ($parent) => [
                                $parent->id => "{$parent->user?->full_name}"
                            ])
                        )
                        ->getOptionLabelUsing( fn ($value) =>
                            ParentModel::find($value)->user?->full_name ?? 'unknown parent'
                        )
                   )
                   ->schema(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Select::make('relationship')
                            ->options([
                                'father' => 'Father',
                                'mother' => 'Mother',
                                'guardian' => 'Guardian',
                                'other' => 'Other',
                            ])
                            ->required()
                            ->default('guardian'),
                        
                        Toggle::make('is_primary_contact')
                            ->label('Primary Contact')
                            ->default(false),
                        
                        Toggle::make('can_view_grades')
                            ->label('Can View Grades')
                            ->default(true),
                        
                        Toggle::make('can_view_attendance')
                            ->label('Can View Attendance')
                            ->default(true),
                    ])
                    // ->preloadRecordSelect(),
            ])
            ->recordActions([
                EditAction::make(),
                DetachAction::make()
                   ->label('Unlink')
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
