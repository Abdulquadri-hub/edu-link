<?php

namespace App\Filament\Parent\Resources\LinkChildren\Schemas;

use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;

class LinkChildInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Request Status')
                    ->schema([
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn ($record) => $record->statusColor)
                            ->formatStateUsing(fn ($record) => $record->statusText)
                            ->size(TextSize::Large),
                        
                        TextEntry::make('created_at')
                            ->label('Requested On')
                            ->dateTime('M d, Y H:i')
                            ->helperText(fn ($record) => $record->created_at->diffForHumans()),
                        
                        TextEntry::make('reviewed_at')
                            ->label('Reviewed On')
                            ->dateTime('M d, Y H:i')
                            ->placeholder('Not yet reviewed')
                            ->helperText(fn ($record) => $record->reviewed_at?->diffForHumans())
                            ->visible(fn ($record) => $record->reviewed_at),
                        
                        TextEntry::make('reviewer.full_name')
                            ->label('Reviewed By')
                            ->placeholder('Pending review')
                            ->visible(fn ($record) => $record->reviewed_by),
                    ])
                    ->columns(2),

                Section::make('Student Information')
                    ->schema([
                        TextEntry::make('student.student_id')
                            ->label('Student ID')
                            ->copyable()
                            ->size(TextSize::Large)
                            ->weight('bold'),
                        
                        TextEntry::make('student.user.full_name')
                            ->label('Student Name')
                            ->size(TextSize::Large),
                        
                        TextEntry::make('student.user.email')
                            ->label('Email')
                            ->copyable()
                            ->icon('heroicon-o-envelope'),
                        
                        TextEntry::make('student.academicLevel.display_name')
                            ->label('Grade Level')
                            ->placeholder('Not assigned'),
                        
                        TextEntry::make('student.enrollment_status')
                            ->label('Status')
                            ->badge()
                            ->color(fn ($state) => match($state) {
                                'active' => 'success',
                                'graduated' => 'info',
                                'dropped' => 'warning',
                                'suspended' => 'danger',
                            }),
                    ])
                    ->columns(3),

                Section::make('Relationship Details')
                    ->schema([
                        TextEntry::make('relationship')
                            ->badge()
                            ->color('info')
                            ->formatStateUsing(fn ($state) => ucfirst($state)),
                        
                        IconEntry::make('is_primary_contact')
                            ->label('Primary Contact')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('gray'),
                        
                        IconEntry::make('can_view_grades')
                            ->label('Can View Grades')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('gray'),
                        
                        IconEntry::make('can_view_attendance')
                            ->label('Can View Attendance')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('gray'),
                    ])
                    ->columns(4),

                Section::make('Your Message')
                    ->schema([
                        TextEntry::make('parent_message')
                            ->label('')
                            ->columnSpanFull()
                            ->placeholder('No message provided'),
                    ])
                    ->visible(fn ($record) => !empty($record->parent_message))
                    ->collapsible(),

                Section::make('Admin Review')
                    ->schema([
                        TextEntry::make('admin_notes')
                            ->label('Admin Notes')
                            ->columnSpanFull()
                            ->placeholder('No notes'),
                    ])
                    ->visible(fn ($record) => !empty($record->admin_notes) && $record->status !== 'pending')
                    ->collapsible(),
            ]);
    }
}
