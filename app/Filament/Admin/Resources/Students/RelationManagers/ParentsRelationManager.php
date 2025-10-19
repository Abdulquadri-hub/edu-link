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

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Link Parent')
                    ->schema([
                        Select::make('parent_id')
                            ->label('Parent')
                            ->required()
                            ->searchable()
                            ->getSearchResultsUsing(fn (string $search) => 
                                ParentModel::whereHas('user', fn ($q) =>
                                    $q->where('user_type', 'parent')->where('first_name', 'like', "%{$search}%")
                                )
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(fn ($parent) => [$parent->id => $parent->user->full_name])
                            )
                            ->getOptionLabelUsing(fn ($value) => 
                                ParentModel::find($value)?->user?->full_name
                            )
                            ->preload(),
                        
                        Select::make('relationship')
                            ->options([
                                'father' => 'Father',
                                'mother' => 'Mother',
                                'guardian' => 'Guardian',
                                'other' => 'Other',
                            ])
                            ->required()
                            ->native(false)
                            ->default('guardian'),
                        
                        Toggle::make('is_primary_contact')
                            ->label('Primary Contact')
                            ->inline(false),
                        
                        Toggle::make('can_view_grades')
                            ->default(true)
                            ->inline(false),
                        
                        Toggle::make('can_view_attendance')
                            ->default(true)
                            ->inline(false),
                    ])
                    ->columns(2),
            ]);
    }

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
                // CreateAction::make()->label('Link Parent'),
                AttachAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DetachAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
