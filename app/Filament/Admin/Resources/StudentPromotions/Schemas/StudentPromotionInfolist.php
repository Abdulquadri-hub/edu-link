<?php

namespace App\Filament\Admin\Resources\StudentPromotions\Schemas;

use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;

class StudentPromotionInfolist
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
                            ->size(TextSize::Large)
                            ->weight('bold'),
                        
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn ($record) => $record->statusColor)
                            ->formatStateUsing(fn ($record) => $record->statusText)
                            ->size(TextSize::Large),
                        
                        TextEntry::make('promotion_type')
                            ->label('Type')
                            ->badge()
                            ->color('info')
                            ->formatStateUsing(fn ($record) => $record->promotionTypeText),
                    ])
                    ->columns(3),

                Section::make('Student Details')
                    ->schema([
                        TextEntry::make('student.student_id')
                            ->label('Student ID')
                            ->copyable(),
                        
                        TextEntry::make('student.user.full_name')
                            ->label('Student Name')
                            ->size(TextSize::Large),
                        
                        TextEntry::make('student.user.email')
                            ->label('Email')
                            ->copyable(),
                        
                        TextEntry::make('student.enrollment_status')
                            ->badge(),
                    ])
                    ->columns(2),

                Section::make('Grade Level Change')
                    ->schema([
                        TextEntry::make('fromLevel.display_name')
                            ->label('From Level')
                            ->size(TextSize::Large)
                            ->badge()
                            ->color('gray')
                            ->placeholder('None'),
                        
                        TextEntry::make('arrow')
                            ->label('')
                            ->formatStateUsing(fn () => '→')
                            ->size(TextSize::Large)
                            ->weight('bold'),
                        
                        TextEntry::make('toLevel.display_name')
                            ->label('To Level')
                            ->size(TextSize::Large)
                            ->badge()
                            ->color('success'),
                        
                        TextEntry::make('fromLevel.grade_number')
                            ->label('From Grade Number')
                            ->placeholder('N/A'),
                        
                        TextEntry::make('grade_arrow')
                            ->label('')
                            ->formatStateUsing(fn () => '→'),
                        
                        TextEntry::make('toLevel.grade_number')
                            ->label('To Grade Number'),
                    ])
                    ->columns(6),

                Section::make('Academic Information')
                    ->schema([
                        TextEntry::make('academic_year')
                            ->label('Academic Year')
                            ->placeholder('Not specified'),
                        
                        TextEntry::make('final_gpa')
                            ->label('Final GPA')
                            ->placeholder('Not recorded')
                            ->color(fn ($state) => match(true) {
                                !$state => 'gray',
                                $state >= 3.5 => 'success',
                                $state >= 3.0 => 'info',
                                $state >= 2.5 => 'warning',
                                default => 'danger',
                            }),
                        
                        IconEntry::make('auto_update_enrollments')
                            ->label('Auto Update Enrollments')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('gray'),
                    ])
                    ->columns(3),

                Section::make('Dates')
                    ->schema([
                        TextEntry::make('promotion_date')
                            ->label('Promotion Date')
                            ->dateTime('M d, Y H:i')
                            ->helperText(fn ($record) => $record->promotion_date->diffForHumans()),
                        
                        TextEntry::make('effective_date')
                            ->label('Effective Date')
                            ->dateTime('M d, Y')
                            ->helperText('When the promotion takes effect'),
                        
                        TextEntry::make('approved_at')
                            ->label('Approved On')
                            ->dateTime('M d, Y H:i')
                            ->placeholder('Not yet approved')
                            ->visible(fn ($record) => $record->approved_at),
                    ])
                    ->columns(3),

                Section::make('People')
                    ->schema([
                        TextEntry::make('promoter.full_name')
                            ->label('Promoted By')
                            ->helperText(fn ($record) => $record->promoter->user_type),
                        
                        TextEntry::make('approver.full_name')
                            ->label('Approved By')
                            ->placeholder('Pending approval')
                            ->visible(fn ($record) => $record->approved_by),
                    ])
                    ->columns(2),

                Section::make('Notes')
                    ->schema([
                        TextEntry::make('promotion_notes')
                            ->label('Promotion Notes')
                            ->columnSpanFull()
                            ->placeholder('No notes'),
                    ])
                    ->visible(fn ($record) => !empty($record->promotion_notes))
                    ->collapsible(),

                Section::make('Rejection Information')
                    ->schema([
                        TextEntry::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->columnSpanFull()
                            ->color('danger'),
                    ])
                    ->visible(fn ($record) => $record->isRejected()),
            ]);
    }
}
