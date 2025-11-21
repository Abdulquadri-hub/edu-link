<?php

namespace App\Filament\Parent\Resources\ChildPromotions\Schemas;

use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class ChildPromotionInfolist
{
    public static function configure(Schema $schema): Schema
    {
         return $schema
            ->components([
                Section::make('Promotion Information')
                    ->schema([
                        TextEntry::make('promotion_code')
                            ->label('Promotion Code')
                            ->copyable()
                            ->size(\Filament\Support\Enums\TextSize::Large)
                            ->weight('bold'),
                        
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn ($record) => $record->statusColor)
                            ->size(\Filament\Support\Enums\TextSize::Large),
                        
                        TextEntry::make('promotion_type')
                            ->label('Type')
                            ->badge()
                            ->formatStateUsing(fn ($record) => $record->promotionTypeText),
                    ])
                    ->columns(3),

                Section::make('Child Information')
                    ->schema([
                        TextEntry::make('student.student_id')
                            ->label('Student ID')
                            ->copyable(),
                        
                        TextEntry::make('student.user.full_name')
                            ->label('Name')
                            ->size(\Filament\Support\Enums\TextSize::Large),
                    ])
                    ->columns(2),

                Section::make('Grade Level Change')
                    ->schema([
                        TextEntry::make('fromLevel.display_name')
                            ->label('Previous Level')
                            ->size(\Filament\Support\Enums\TextSize::Large)
                            ->badge()
                            ->color('gray')
                            ->placeholder('None'),
                        
                        TextEntry::make('arrow')
                            ->label('')
                            ->formatStateUsing(fn () => '→')
                            ->size(\Filament\Support\Enums\TextSize::Large),
                        
                        TextEntry::make('toLevel.display_name')
                            ->label('New Level')
                            ->size(\Filament\Support\Enums\TextSize::Large)
                            ->badge()
                            ->color('success'),
                    ])
                    ->columns(3),

                Section::make('Details')
                    ->schema([
                        TextEntry::make('academic_year')
                            ->label('Academic Year')
                            ->placeholder('Not specified'),
                        
                        TextEntry::make('final_gpa')
                            ->label('Final GPA')
                            ->placeholder('Not recorded'),
                        
                        TextEntry::make('promotion_date')
                            ->label('Promotion Date')
                            ->dateTime('M d, Y')
                            ->helperText(fn ($record) => $record->promotion_date->diffForHumans()),
                        
                        TextEntry::make('effective_date')
                            ->label('Effective Date')
                            ->date('M d, Y'),
                    ])
                    ->columns(2),

                Section::make('Notes')
                    ->schema([
                        TextEntry::make('promotion_notes')
                            ->label('')
                            ->columnSpanFull()
                            ->placeholder('No notes'),
                    ])
                    ->visible(fn ($record) => !empty($record->promotion_notes))
                    ->collapsible(),

                Section::make('Rejection Information')
                    ->schema([
                        TextEntry::make('rejection_reason')
                            ->label('Reason')
                            ->columnSpanFull()
                            ->color('danger'),
                    ])
                    ->visible(fn ($record) => $record->isRejected()),
            ]);
    }
}
