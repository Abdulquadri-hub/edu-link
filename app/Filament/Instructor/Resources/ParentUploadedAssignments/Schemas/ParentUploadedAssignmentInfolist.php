<?php

namespace App\Filament\Instructor\Resources\ParentUploadedAssignments\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class ParentUploadedAssignmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Upload Information')
                    ->schema([
                        TextEntry::make('student.user.full_name')
                            ->label('Student'),
                        
                        TextEntry::make('parent.user.full_name')
                            ->label('Uploaded By (Parent)')
                            ->helperText(fn ($record) => "Parent Email: {$record->parent->user->email}"),
                        
                        TextEntry::make('assignment.title')
                            ->label('Assignment'),
                        
                        TextEntry::make('assignment.course.title')
                            ->label('Course'),
                        
                        TextEntry::make('submitted_at')
                            ->dateTime('M d, Y H:i')
                            ->helperText(fn ($record) => $record->submitted_at?->diffForHumans()),
                        
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn ($record) => $record->statusColor),
                    ])
                    ->columns(3),

                Section::make('Uploaded Files')
                    ->schema([
                        TextEntry::make('attachments')
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

                Section::make('Parent Notes')
                    ->schema([
                        TextEntry::make('parent_notes')
                            ->label('')
                            ->columnSpanFull()
                            ->placeholder('No notes provided'),
                    ])
                    ->visible(fn ($record) => !empty($record->parent_notes)),
            ]);
    }
}
