<?php

namespace App\Filament\Student\Resources\Assignments\Schemas;

use App\Models\Course;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;

class AssignmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
           ->components([
                Section::make('Assignment Details')
                    ->schema([
                        TextEntry::make('title')
                            ->size(TextSize::Large)
                            ->weight('bold')
                            ->columnSpanFull(),
                        
                        TextEntry::make('course.title')
                            ->label('Course'),
                        
                        TextEntry::make('type')
                            ->badge(),
                        
                        TextEntry::make('max_score')
                            ->suffix(' points'),
                        
                        TextEntry::make('due_at')
                            ->dateTime('M d, Y H:i')
                            ->color(fn ($record) => $record->due_at->isPast() ? 'danger' : 'success')
                            ->helperText(fn ($record) => $record->due_at->isPast() ? 'Overdue' : $record->due_at->diffForHumans()),
                        
                        TextEntry::make('allows_late_submission')
                            ->label('Late Submission')
                            ->formatStateUsing(fn ($state) => $state ? 'Allowed' : 'Not Allowed')
                            ->badge()
                            ->color(fn ($state) => $state ? 'success' : 'danger'),
                    ])
                    ->columns(3),

                Section::make('Description')
                    ->schema([
                        TextEntry::make('description')
                            ->html()
                            ->columnSpanFull(),
                    ]),

                Section::make('Instructions')
                    ->schema([
                        TextEntry::make('instructions')
                            ->html()
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => !empty($record->instructions)),

                Section::make('Attachments')
                    ->schema([
                        TextEntry::make('attachments')
                            ->label('')
                            ->formatStateUsing(function ($state) {
                                if (empty($state)) return 'No attachments';
                                
                                return collect($state)->map(function ($file) {
                                    $filename = basename($file);
                                    $url = asset('storage/' . $file);
                                    return "<a href='{$url}' target='_blank' class='text-primary-600 hover:underline'>📎 {$filename}</a>";
                                })->join('<br>');
                            })
                            ->html()
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => !empty($record->attachments)),

                Section::make('My Submission')
                    ->schema([
                        TextEntry::make('submission.submitted_at')
                            ->label('Submitted')
                            ->dateTime('M d, Y H:i')
                            ->getStateUsing(function ($record) {
                                $submission = $record->submissions->first();
                                return $submission?->submitted_at;
                            }),
                        
                        TextEntry::make('submission.status')
                            ->badge()
                            ->getStateUsing(function ($record) {
                                return $record->submissions->first()?->status;
                            }),
                        
                        TextEntry::make('submission.is_late')
                            ->label('Status')
                            ->formatStateUsing(fn ($state) => $state ? 'Late Submission' : 'On Time')
                            ->badge()
                            ->color(fn ($state) => $state ? 'danger' : 'success')
                            ->getStateUsing(function ($record) {
                                return $record->submissions->first()?->is_late;
                            }),
                        
                        TextEntry::make('grade.percentage')
                            ->label('Grade')
                            ->suffix('%')
                            ->getStateUsing(function ($record) {
                                $submission = $record->submissions->first();
                                return $submission?->grade?->percentage;
                            })
                            ->visible(fn ($record) => $record->submissions->first()?->grade?->is_published),
                    ])
                    ->columns(4)
                    ->visible(fn ($record) => $record->submissions->isNotEmpty()),
            ]);
    }
}
