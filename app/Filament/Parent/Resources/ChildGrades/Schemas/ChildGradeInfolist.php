<?php

namespace App\Filament\Parent\Resources\ChildGrades\Schemas;

use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class ChildGradeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Grade Details')
                    ->schema([
                        TextEntry::make('submission.student.user.full_name')
                            ->label('Student')
                            ->size(TextSize::Large)
                            ->weight('bold'),
                        
                        TextEntry::make('submission.assignment.title')
                            ->label('Assignment')
                            ->size(TextSize::Large),
                        
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
                            ->badge()
                            ->size(TextSize::Large),
                        
                        TextEntry::make('published_at')
                            ->dateTime('M d, Y H:i')
                            ->helperText(fn ($record) => $record->published_at->diffForHumans()),
                        
                        TextEntry::make('instructor.user.full_name')
                            ->label('Instructor'),
                    ])
                    ->columns(3),

                Section::make('Instructor Feedback')
                    ->schema([
                        TextEntry::make('feedback')
                            ->html()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
