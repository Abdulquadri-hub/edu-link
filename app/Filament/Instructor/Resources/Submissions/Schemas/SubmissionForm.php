<?php

namespace App\Filament\Instructor\Resources\Submissions\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Infolists\Components\TextEntry;

class SubmissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
               Section::make('Grade Submission')
                    ->schema([
                       TextEntry::make('student_info')
                            ->label('Student')
                            ->state(fn ($record) => $record->student->student_id . ' - ' . $record->student->user->full_name),
                        
                       TextEntry::make('assignment_info')
                            ->label('Assignment')
                            ->state(fn ($record) => $record->assignment->title . ' (' . $record->assignment->course->course_code . ')'),
                        
                       TextEntry::make('submission_time')
                            ->label('Submitted')
                            ->state(fn ($record) => $record->submitted_at->format('M d, Y H:i') . 
                                ($record->is_late ? ' ⚠️ LATE' : ' ✅ On Time')),
                        
                       TextEntry::make('max_score_display')
                            ->label('Maximum Score')
                            ->state(fn ($record) => $record->assignment->max_score . ' points'),
                    ])
                    ->columns(2),
            ]);
    }
}
