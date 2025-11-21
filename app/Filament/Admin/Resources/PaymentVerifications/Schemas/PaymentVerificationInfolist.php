<?php

namespace App\Filament\Admin\Resources\PaymentVerifications\Schemas;

use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;

class PaymentVerificationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Payment Information')
                    ->schema([
                       TextEntry::make('payment_reference')
                            ->copyable(),
                       TextEntry::make('status')
                            ->badge()
                            ->color(fn ($record) => $record->statusColor),
                       TextEntry::make('amount')
                            ->money(fn ($record) => $record->currency)
                            ->size(\Filament\Support\Enums\TextSize::Large)
                            ->weight('bold'),
                       TextEntry::make('payment_date')
                            ->date('M d, Y'),
                       TextEntry::make('payment_method')
                            ->badge(),
                    ])
                    ->columns(3),

                Section::make('People')
                    ->schema([
                       TextEntry::make('parent.user.full_name')
                            ->label('Parent'),
                       TextEntry::make('parent.user.email')
                            ->copyable(),
                       TextEntry::make('student.user.full_name')
                            ->label('Student'),
                       TextEntry::make('student.student_id')
                            ->copyable(),
                       TextEntry::make('course.title')
                            ->label('Course'),
                    ])
                    ->columns(2),

                Section::make('Receipt')
                    ->schema([
                       ImageEntry::make('receipt_path')
                            ->disk('public')
                            ->imageHeight(400)
                            ->columnSpanFull(),
                    ]),

                Section::make('Notes')
                    ->schema([
                       TextEntry::make('parent_notes')
                            ->label('Parent Notes')
                            ->columnSpanFull()
                            ->placeholder('No notes'),
                       TextEntry::make('admin_notes')
                            ->label('Admin Notes')
                            ->columnSpanFull()
                            ->placeholder('No notes'),
                    ])
                    ->collapsible(),
            ]);
    }
}
