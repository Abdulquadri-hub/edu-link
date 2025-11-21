<?php

namespace App\Filament\Admin\Resources\ChildLinkingRequests\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;

class ChildLinkingRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
             return $schema
            ->components([
               Section::make('Request Information')
                    ->schema([
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn ($record) => $record->statusColor)
                            ->size(\Filament\Support\Enums\TextSize::Large),
                        
                        TextEntry::make('created_at')
                            ->dateTime('M d, Y H:i')
                            ->helperText(fn ($record) => $record->created_at->diffForHumans()),
                    ])
                    ->columns(2),

               Section::make('Parent Information')
                    ->schema([
                        TextEntry::make('parent.user.full_name')
                            ->label('Name'),
                        TextEntry::make('parent.user.email')
                            ->label('Email')
                            ->copyable(),
                        TextEntry::make('parent.user.phone')
                            ->label('Phone')
                            ->copyable(),
                        TextEntry::make('parent.parent_id')
                            ->label('Parent ID')
                            ->copyable(),
                    ])
                    ->columns(2),

               Section::make('Student Information')
                    ->schema([
                        TextEntry::make('student.student_id')
                            ->label('Student ID')
                            ->copyable(),
                        TextEntry::make('student.user.full_name')
                            ->label('Name'),
                        TextEntry::make('student.user.email')
                            ->label('Email')
                            ->copyable(),
                        TextEntry::make('student.academicLevel.display_name')
                            ->label('Grade Level'),
                    ])
                    ->columns(2),

               Section::make('Relationship Details')
                    ->schema([
                        TextEntry::make('relationship')
                            ->badge(),
                        IconEntry::make('is_primary_contact')
                            ->boolean(),
                        IconEntry::make('can_view_grades')
                            ->boolean(),
                        IconEntry::make('can_view_attendance')
                            ->boolean(),
                    ])
                    ->columns(4),

               Section::make('Parent Message')
                    ->schema([
                        TextEntry::make('parent_message')
                            ->label('')
                            ->columnSpanFull()
                            ->placeholder('No message provided'),
                    ])
                    ->visible(fn ($record) => !empty($record->parent_message))
                    ->collapsible(),

               Section::make('Review Information')
                    ->schema([
                        TextEntry::make('reviewer.full_name')
                            ->label('Reviewed By'),
                        TextEntry::make('reviewed_at')
                            ->dateTime('M d, Y H:i')
                            ->helperText(fn ($record) => $record->reviewed_at?->diffForHumans()),
                        TextEntry::make('admin_notes')
                            ->label('Admin Notes')
                            ->columnSpanFull()
                            ->placeholder('No notes'),
                    ])
                    ->visible(fn ($record) => $record->status !== 'pending')
                    ->columns(2),
            ]);
    }
}
