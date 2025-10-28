<?php

namespace App\Filament\Student\Resources\ClassSessions\Schemas;

use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class ClassSessionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
           ->components([
                Section::make('Class Details')
                    ->schema([
                        TextEntry::make('title')
                            ->size(TextSize::Large)
                            ->weight('bold')
                            ->columnSpanFull(),
                        
                        TextEntry::make('course.title')
                            ->label('Course'),
                        
                        TextEntry::make('instructor.user.full_name')
                            ->label('Instructor'),
                        
                        TextEntry::make('scheduled_at')
                            ->dateTime('l, F d, Y - H:i A')
                            ->label('Scheduled')
                            ->helperText(fn ($record) => $record->scheduled_at->diffForHumans()),
                        
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn ($state) => match($state) {
                                'scheduled' => 'info',
                                'in-progress' => 'warning',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                            }),
                        
                        TextEntry::make('duration_minutes')
                            ->label('Duration')
                            ->suffix(' minutes')
                            ->visible(fn ($record) => $record->status === 'completed'),
                        
                        TextEntry::make('description')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Section::make('Join Class')
                    ->schema([
                        TextEntry::make('google_meet_link')
                            ->label('Meeting Link')
                            ->url(fn ($state) => $state)
                            ->openUrlInNewTab()
                            ->copyable()
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => !empty($record->google_meet_link) && 
                        in_array($record->status, ['scheduled', 'in-progress'])),

                Section::make('Additional Notes')
                    ->schema([
                        TextEntry::make('notes')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => !empty($record->notes))
                    ->collapsible(),
            ]);
    }
}
