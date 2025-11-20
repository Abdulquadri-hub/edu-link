<?php

namespace App\Filament\Instructor\Resources\ParentUploadedAssignments;

use UnitEnum;
use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use App\Models\ParentAssignment;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class ParentUploadedAssignmentResource extends Resource
{
    protected static ?string $model = ParentAssignment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::InboxArrowDown;
    
    protected static string|UnitEnum|null $navigationGroup = 'Teaching';
    protected static ?string $navigationLabel = 'Parent Uploads';
    protected static ?int $navigationSort = 5;
    protected static ?string $modelLabel = 'Parent Upload';
    protected static ?string $pluralModelLabel = 'Parent Uploads';

    public static function getEloquentQuery(): Builder
    {
        $instructor = Auth::user()->instructor;
        
        // Get parent assignments for courses the instructor teaches
        return parent::getEloquentQuery()
            ->whereHas('assignment.course.instructors', function ($query) use ($instructor) {
                $query->where('instructor_id', $instructor->id);
            })
            ->where('status', '!=', 'pending') // Only show submitted ones
            ->with(['student.user', 'parent.user', 'assignment.course', 'submission.grade']);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('student.user.full_name')
                    ->label('Student')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                
                \Filament\Tables\Columns\TextColumn::make('parent.user.full_name')
                    ->label('Uploaded By (Parent)')
                    ->searchable(['first_name', 'last_name'])
                    ->description(fn ($record) => 'Parent'),
                
                \Filament\Tables\Columns\TextColumn::make('assignment.title')
                    ->label('Assignment')
                    ->searchable()
                    ->limit(40)
                    ->description(fn ($record) => $record->assignment->course->course_code),
                
                \Filament\Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->description(fn ($record) => $record->submitted_at->diffForHumans()),
                
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'info' => 'submitted',
                        'success' => 'graded',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                
                \Filament\Tables\Columns\TextColumn::make('submission.grade.percentage')
                    ->label('Grade')
                    ->suffix('%')
                    ->color('success')
                    ->weight('bold')
                    ->placeholder('Not graded')
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('course')
                    ->label('Course')
                    ->relationship('assignment.course', 'title')
                    ->searchable()
                    ->preload(),
                
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'submitted' => 'Awaiting Grading',
                        'graded' => 'Graded',
                    ]),
            ])
            ->recordActions([
                \Filament\Actions\ViewAction::make(),
                
                \Filament\Actions\Action::make('grade')
                    ->label('Grade')
                    ->icon('heroicon-o-academic-cap')
                    ->color('success')
                    ->url(fn ($record) => route('filament.instructor.resources.submissions.view', [
                        'record' => $record->submission_id
                    ]))
                    ->visible(fn ($record) => $record->submission_id && !$record->submission->grade),
            ])
            ->toolbarActions([])
            ->defaultSort('submitted_at', 'desc');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Upload Information')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('student.user.full_name')
                            ->label('Student'),
                        
                        \Filament\Infolists\Components\TextEntry::make('parent.user.full_name')
                            ->label('Uploaded By (Parent)')
                            ->helperText(fn ($record) => "Parent Email: {$record->parent->user->email}"),
                        
                        \Filament\Infolists\Components\TextEntry::make('assignment.title')
                            ->label('Assignment'),
                        
                        \Filament\Infolists\Components\TextEntry::make('assignment.course.title')
                            ->label('Course'),
                        
                        \Filament\Infolists\Components\TextEntry::make('submitted_at')
                            ->dateTime('M d, Y H:i')
                            ->helperText(fn ($record) => $record->submitted_at->diffForHumans()),
                        
                        \Filament\Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn ($record) => $record->statusColor),
                    ])
                    ->columns(3),

                \Filament\Schemas\Components\Section::make('Uploaded Files')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('attachments')
                            ->label('')
                            ->formatStateUsing(function ($state) {
                                if (empty($state)) return 'No files';
                                
                                return collect($state)->map(function ($file) {
                                    $filename = basename($file);
                                    $url = asset('storage/' . $file);
                                    return "<a href='{$url}' target='_blank' class='text-primary-600 hover:underline'>📎 {$filename}</a>";
                                })->join('<br>');
                            })
                            ->html()
                            ->columnSpanFull(),
                    ]),

                \Filament\Schemas\Components\Section::make('Parent Notes')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('parent_notes')
                            ->label('')
                            ->columnSpanFull()
                            ->placeholder('No notes provided'),
                    ])
                    ->visible(fn ($record) => !empty($record->parent_notes)),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListParentUploadedAssignments::route('/'),
            'view' => Pages\ViewParentUploadedAssignment::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $instructor = Auth::user()->instructor;
        
        $count = static::getModel()::whereHas('assignment.course.instructors', function ($query) use ($instructor) {
            $query->where('instructor_id', $instructor->id);
        })
        ->where('status', 'submitted')
        ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }
}

// Pages
namespace App\Filament\Instructor\Resources\ParentUploadedAssignments\Pages;

use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ViewRecord;

class ListParentUploadedAssignments extends ListRecords
{
    protected static string $resource = \App\Filament\Instructor\Resources\ParentUploadedAssignments\ParentUploadedAssignmentResource::class;
}

class ViewParentUploadedAssignment extends ViewRecord
{
    protected static string $resource = \App\Filament\Instructor\Resources\ParentUploadedAssignments\ParentUploadedAssignmentResource::class;
}