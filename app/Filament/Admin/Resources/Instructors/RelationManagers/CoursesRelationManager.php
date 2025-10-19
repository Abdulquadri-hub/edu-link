<?php

namespace App\Filament\Admin\Resources\Instructors\RelationManagers;

use App\Models\Course;
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
use Filament\Forms\Components\DatePicker;
use Filament\Resources\RelationManagers\RelationManager;

class CoursesRelationManager extends RelationManager
{
    protected static string $relationship = 'courses';
    protected static ?string $recordTitleAttribute = 'title';


    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Course Assignment')
                    ->schema([
                        Select::make('course_id')
                            ->label('Select Course')
                            ->required()
                            ->searchable()
                            ->getSearchResultsUsing( fn (string $search) =>  
                                Course::where('title', 'like', "%{$search}%")
                                    ->orWhere('course_code', 'like', "%{$search}%") ->limit(5)
                                    ->get()
                                    ->mapWithKeys(fn ($course) => [
                                        $course->id => "{$course->title} ({$course->course_code})"
                                    ])
                            )
                            ->getOptionLabelUsing(fn ($value) => 
                                Course::find($value)?->title
                            )
                            ->preload(),
                        
                        DatePicker::make('assigned_date')
                            ->required()
                            ->native(false)
                            ->default(now()),
                        
                        Toggle::make('is_primary_instructor')
                            ->default(true)
                            ->inline(false),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('course_id')
            ->columns([
                TextColumn::make('course_code')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('title')
                    ->searchable()
                    ->limit(40),
                
                TextColumn::make('category'),
                
                TextColumn::make('level'),
                
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'active',
                        'danger' => 'archived',
                    ]),
                
                IconColumn::make('pivot.is_primary_instructor')
                    ->boolean()
                    ->label('Primary'),
                
                TextColumn::make('pivot.assigned_date')
                    ->date()
                    ->label('Assigned')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Attach Course')
                    ->color('success')
                    ->icon('heroicon-o-link')
                    ->recordSelectSearchColumns(['title', 'course_code']) 
                    ->recordTitle(fn ($record) => "{$record->course_code} - {$record->title}")
                    ->recordSelectOptionsQuery(
                        fn ($query) => $query->where('status', 'active')->orderBy('title')
                    ),
            ])
            ->recordActions([
                DetachAction::make()
                    ->label('Detach Course')
                    ->color('danger')
                    ->icon('heroicon-o-link'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
