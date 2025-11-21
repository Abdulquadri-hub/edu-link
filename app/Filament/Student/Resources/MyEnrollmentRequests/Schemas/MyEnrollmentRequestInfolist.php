<?php

namespace App\Filament\Student\Resources\MyEnrollmentRequests\Schemas;

use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class MyEnrollmentRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Request Information')
                    ->schema([
                        TextEntry::make('request_code')
                            ->label('Request Code')
                            ->copyable()
                            ->size(\Filament\Support\Enums\TextSize::Large)
                            ->weight('bold'),
                        
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn ($record) => $record->statusColor)
                            ->formatStateUsing(fn ($record) => $record->statusText)
                            ->size(\Filament\Support\Enums\TextSize::Large),
                        
                        TextEntry::make('created_at')
                            ->label('Requested On')
                            ->dateTime('M d, Y H:i')
                            ->helperText(fn ($record) => $record->created_at->diffForHumans()),
                    ])
                    ->columns(3),

                Section::make('Course Details')
                    ->schema([
                        TextEntry::make('course.course_code')
                            ->label('Course Code')
                            ->copyable(),
                        
                        TextEntry::make('course.title')
                            ->label('Course Title')
                            ->size(\Filament\Support\Enums\TextSize::Large),
                        
                        TextEntry::make('frequency_preference')
                            ->label('Frequency')
                            ->formatStateUsing(fn ($record) => $record->frequencyText)
                            ->badge()
                            ->color('info'),
                        
                        TextEntry::make('quoted_price')
                            ->label('Price')
                            ->formatStateUsing(fn ($record) => $record->formattedPrice)
                            ->size(\Filament\Support\Enums\TextSize::Large)
                            ->weight('bold')
                            ->color('success'),
                    ])
                    ->columns(2),

                Section::make('Your Message')
                    ->schema([
                        TextEntry::make('student_message')
                            ->label('')
                            ->columnSpanFull()
                            ->placeholder('No message provided'),
                    ])
                    ->visible(fn ($record) => !empty($record->student_message))
                    ->collapsible(),

                Section::make('Processing Information')
                    ->schema([
                        TextEntry::make('processor.full_name')
                            ->label('Processed By')
                            ->placeholder('Pending'),
                        
                        TextEntry::make('processed_at')
                            ->label('Processed On')
                            ->dateTime('M d, Y H:i')
                            ->placeholder('Pending'),
                        
                        TextEntry::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->columnSpanFull()
                            ->color('danger')
                            ->visible(fn ($record) => $record->isRejected()),
                    ])
                    ->visible(fn ($record) => !$record->isPending())
                    ->columns(2),

                Section::make('Enrollment Created')
                    ->schema([
                        TextEntry::make('enrollment.enrolled_at')
                            ->label('Enrolled On')
                            ->dateTime('M d, Y H:i'),
                        
                        TextEntry::make('enrollment.status')
                            ->badge()
                            ->color('success'),
                    ])
                    ->visible(fn ($record) => $record->isApproved() && $record->enrollment_id)
                    ->columns(2),
            ]);
    }
}
