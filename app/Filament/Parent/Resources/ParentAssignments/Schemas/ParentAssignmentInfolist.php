<?php

namespace App\Filament\Parent\Resources\ParentAssignments\Schemas;

use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class ParentAssignmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Upload Information')
                    ->schema([
                        TextEntry::make('student.user.full_name')
                            ->label('Child')
                            ->size(TextSize::Large)
                            ->weight('bold'),
                        
                        TextEntry::make('assignment.title')
                            ->label('Assignment')
                            ->size(TextSize::Large),
                        
                        TextEntry::make('assignment.course.title')
                            ->label('Course'),
                        
                        TextEntry::make('assignment.due_at')
                            ->label('Due Date')
                            ->dateTime('M d, Y H:i')
                            ->color(fn ($record) => $record->assignment->due_at->isPast() ? 'danger' : 'success')
                            ->helperText(fn ($record) => $record->assignment?->due_at->isPast() ? 'Overdue' : $record->assignment?->due_at->diffForHumans()),
                        
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn ($record) => $record->statusColor)
                            ->formatStateUsing(fn ($record) => $record->getSubmissionStatusText())
                            ->size(TextSize::Large),
                        
                        TextEntry::make('uploaded_at')
                            ->label('Uploaded At')
                            ->dateTime('M d, Y H:i')
                            ->helperText(fn ($record) => $record->uploaded_at->diffForHumans()),
                    ])
                    ->columns(3),

                Section::make('Assignment Details')
                    ->schema([
                        TextEntry::make('assignment.description')
                            ->label('Description')
                            ->html()
                            ->columnSpanFull(),
                        
                        TextEntry::make('assignment.instructions')
                            ->label('Instructions')
                            ->html()
                            ->columnSpanFull()
                            ->visible(fn ($record) => !empty($record->assignment->instructions)),
                        
                        TextEntry::make('assignment.max_score')
                            ->label('Maximum Score')
                            ->suffix(' points'),
                        
                        TextEntry::make('assignment.type')
                            ->badge(),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Uploaded Files')
                    ->schema([
                        TextEntry::make('attachments')
                            ->label('')
                            ->formatStateUsing(function ($state) {
                                if (empty($state)) return 'No files uploaded';
                                
                                return collect($state)->map(function ($file) {
                                    $filename = basename($file);
                                    $url = asset('storage/' . $file);
                                    return "<a href='{$url}' target='_blank' class='text-primary-600 hover:underline'>📎 {$filename}</a>";
                                })->join('<br>');
                            })
                            ->html()
                            ->columnSpanFull(),
                    ]),

                Section::make('Parent Notes')
                    ->schema([
                        TextEntry::make('parent_notes')
                            ->label('')
                            ->columnSpanFull()
                            ->placeholder('No notes provided'),
                    ])
                    ->visible(fn ($record) => !empty($record->parent_notes))
                    ->collapsible(),

                Section::make('Grading Information')
                    ->schema([
                        TextEntry::make('submission.grade.percentage')
                            ->label('Grade')
                            ->suffix('%')
                            ->size(TextSize::Large)
                            ->weight('bold')
                            ->color(fn ($state) => match(true) {
                                $state >= 90 => 'success',
                                $state >= 80 => 'info',
                                $state >= 70 => 'warning',
                                default => 'danger',
                            }),
                        
                        TextEntry::make('submission.grade.letter_grade')
                            ->label('Letter Grade')
                            ->badge()
                            ->size(TextSize::Large),
                        
                        TextEntry::make('submission.grade.published_at')
                            ->label('Graded On')
                            ->dateTime('M d, Y H:i')
                            ->helperText(fn ($record) => $record->submission?->grade?->published_at?->diffForHumans()),
                        
                        TextEntry::make('submission.grade.feedback')
                            ->label('Instructor Feedback')
                            ->html()
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->visible(fn ($record) => $record->status === 'graded' && $record->submission?->grade?->is_published),
            ]);
    }
}
