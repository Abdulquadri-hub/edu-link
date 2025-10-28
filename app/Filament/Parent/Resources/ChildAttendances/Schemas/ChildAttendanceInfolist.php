<?php

namespace App\Filament\Parent\Resources\ChildAttendances\Schemas;

use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class ChildAttendanceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Attendance Record')
                    ->schema([
                        TextEntry::make('student.user.full_name')
                            ->label('Student')
                            ->size(TextSize::Large),
                        
                        TextEntry::make('classSession.title')
                            ->label('Class')
                            ->size(TextSize::Large),
                        
                        TextEntry::make('classSession.course.title')
                            ->label('Course'),
                        
                        TextEntry::make('classSession.scheduled_at')
                            ->dateTime('l, F d, Y - H:i A')
                            ->label('Class Date'),
                        
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn ($state) => match($state) {
                                'present' => 'success',
                                'late' => 'warning',
                                'absent' => 'danger',
                                'excused' => 'info',
                            })
                            ->size(TextSize::Large),
                        
                        TextEntry::make('joined_at')
                            ->dateTime('H:i A')
                            ->label('Joined At')
                            ->visible(fn ($record) => $record->joined_at),
                        
                        TextEntry::make('duration_minutes')
                            ->suffix(' minutes')
                            ->label('Duration')
                            ->visible(fn ($record) => $record->duration_minutes),
                        
                        TextEntry::make('classSession.instructor.user.full_name')
                            ->label('Instructor'),
                    ])
                    ->columns(3),

                Section::make('Notes')
                    ->schema([
                        TextEntry::make('notes')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => !empty($record->notes))
                    ->collapsible(),
            ]);
    }
}
