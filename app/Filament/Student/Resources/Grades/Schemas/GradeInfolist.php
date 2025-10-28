<?php

namespace App\Filament\Student\Resources\Grades\Schemas;

use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class GradeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
           ->components([
                Section::make('Grade Information')
                    ->schema([
                        TextEntry::make('submission.assignment.title')
                            ->label('Assignment')
                            ->size(TextSize::Large)
                            ->weight('bold')
                            ->columnSpanFull(),
                        
                        
                            TextEntry::make('submission.assignment.course.title')
                            ->label('Course'),
                        
                        
                            TextEntry::make('score')
                            ->label('Score')
                            ->formatStateUsing(fn ($state, $record) => $state . ' / ' . $record->max_score)
                            ->size(TextSize::Large),
                        
                        
                            TextEntry::make('percentage')
                            ->suffix('%')
                            ->size(TextSize::Large)
                            ->weight('bold')
                            ->color(fn ($state) => match(true) {
                                $state >= 90 => 'success',
                                $state >= 80 => 'info',
                                $state >= 70 => 'warning',
                                default => 'danger',
                            }),
                        
                        
                            TextEntry::make('letter_grade')
                            ->label('Letter Grade')
                            ->badge()
                            ->size(TextSize::Large),
                        
                            TextEntry::make('published_at')
                            ->dateTime('M d, Y H:i')
                            ->helperText(fn ($record) => $record->published_at->diffForHumans()),
                        
                        
                            TextEntry::make('instructor.user.full_name')
                            ->label('Graded By'),
                    ])
                    ->columns(3),

                    Section::make('Instructor Feedback')
                    ->schema([
                        TextEntry::make('feedback')
                            ->html()
                            ->columnSpanFull(),
                    ]),

                    Section::make('My Submission')
                        ->schema([
                        TextEntry::make('submission.content')
                            ->label('Content')
                            ->html()
                            ->columnSpanFull(),
                        
                        
                            TextEntry::make('submission.submitted_at')
                            ->dateTime('M d, Y H:i')
                            ->label('Submitted At'),
                        
                        
                            TextEntry::make('submission.is_late')
                            ->label('Status')
                            ->formatStateUsing(fn ($state) => $state ? 'Late Submission' : 'On Time')
                            ->badge()
                            ->color(fn ($state) => $state ? 'danger' : 'success'),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }
}
