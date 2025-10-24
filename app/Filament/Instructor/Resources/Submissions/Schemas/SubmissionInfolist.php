<?php

namespace App\Filament\Instructor\Resources\Submissions\Schemas;

use App\Models\Submission;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;

class SubmissionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Submission Information')
                    ->schema([
                        TextEntry::make('student.student_id')
                            ->label('Student ID'),
                        TextEntry::make('student.user.full_name')
                            ->label('Student Name'),
                        TextEntry::make('assignment.title')
                            ->label('Assignment'),
                        TextEntry::make('assignment.course.title')
                            ->label('Course'),
                        TextEntry::make('submitted_at')
                            ->dateTime('M d, Y H:i')
                            ->label('Submitted At'),
                        TextEntry::make('is_late')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state ? 'Late Submission' : 'On Time')
                            ->color(fn ($state) => $state ? 'danger' : 'success'),
                        TextEntry::make('attempt_number')
                            ->label('Attempt Number'),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn ($state) => match($state) {
                                'submitted' => 'warning',
                                'graded' => 'success',
                                'returned' => 'info',
                                'resubmit' => 'danger',
                            }),
                    ])
                    ->columns(2),

                Section::make('Student Submission')
                    ->schema([
                        TextEntry::make('content')
                            ->label('Content')
                            ->html()
                            ->columnSpanFull()
                            ->default('No text content submitted'),
                        
                        RepeatableEntry::make('attachments')
                            ->label('Attachments')
                            ->schema([
                                TextEntry::make('file')
                                    ->formatStateUsing(function ($state, $record) {
                                        if (empty($record->attachments)) return 'No attachments';
                                        
                                        return collect($record->attachments)->map(function ($file) {
                                            $filename = basename($file);
                                            $url = asset('storage/' . $file);
                                            return "<a href='{$url}' target='_blank' class='text-primary-600 hover:underline'>📎 {$filename}</a>";
                                        })->join('<br>');
                                    })
                                    ->html(),
                            ])
                            ->visible(fn ($record) => !empty($record->attachments))
                            ->columnSpanFull(),
                    ]),

                Section::make('Grading Information')
                    ->schema([
                        TextEntry::make('grade.score')
                            ->label('Score')
                            ->formatStateUsing(fn ($state, $record) => 
                                $state ? $state . ' / ' . $record->grade->max_score : 'Not graded yet'
                            ),
                        TextEntry::make('grade.percentage')
                            ->label('Percentage')
                            ->formatStateUsing(fn ($state) => $state ? $state . '%' : 'N/A')
                            ->badge()
                            ->color(fn ($state) => match(true) {
                                $state >= 90 => 'success',
                                $state >= 80 => 'info',
                                $state >= 70 => 'warning',
                                $state >= 60 => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('grade.letter_grade')
                            ->label('Letter Grade')
                            ->badge(),
                        TextEntry::make('grade.graded_at')
                            ->dateTime('M d, Y H:i')
                            ->label('Graded At'),
                        TextEntry::make('grade.feedback')
                            ->label('Feedback')
                            ->html()
                            ->columnSpanFull(),
                        TextEntry::make('grade.is_published')
                            ->label('Published')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state ? 'Published' : 'Draft')
                            ->color(fn ($state) => $state ? 'success' : 'warning'),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record->grade !== null),
            ]);
    }
}
