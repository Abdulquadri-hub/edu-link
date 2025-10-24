<?php

namespace App\Filament\Admin\Resources\Parents\RelationManagers;

use App\Models\Student;
use Filament\Tables\Table;
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
use Filament\Resources\RelationManagers\RelationManager;

class ChildrenRelationManager extends RelationManager
{
    protected static string $relationship = 'children';
    protected static ?string $inverseRelationship = 'parents';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('student_id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('student_id')
            ->columns([
                TextColumn::make('student_id')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('user.full_name')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                
                TextColumn::make('enrollment_status')
                    ->badge()
                    ->colors([
                        'success' => 'active',
                        'info' => 'graduated',
                        'warning' => 'dropped',
                        'danger' => 'suspended',
                    ]),
                
                TextColumn::make('pivot.relationship')->badge(),
                
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
                //
            ])
            ->headerActions([
                AttachAction::make()
                ->label('Link Child')
                ->color('success')
                ->icon("heroicon-o-link")
                ->recordSelect(fn (Select $select) => $select
                   ->placeholder('Enter child first/last name')
                   ->searchable()
                   ->getSearchResultsUsing( fn (string $search) => 
                      Student::whereHas('user', function ($query) use ($search) {
                        $query->where('user_type', 'student')
                            ->where(function ($student) use ($search) {
                                $student->where('first_name', 'like', "%$search%")
                                   ->orWhere('last_name', 'like', "%$search%");
                            });
                      })
                      ->limit(10)
                      ->get()
                      ->mapWithKeys(
                            fn ($student) => [   
                                $student->id => "{$student->user?->full_name}"
                        ])
                   )
                   ->getOptionLabelUsing(fn ($value) =>
                       Student::find($value)->user?->full_name ?? 'unknown student'
                   )
                )
            ])
            ->recordActions([
                EditAction::make(),
                DetachAction::make()->label('Unlink'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
