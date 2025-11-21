<?php

namespace App\Filament\Parent\Resources\Payments\Schemas;

use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;

class PaymentInfolist
{
     public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Payment Status')
                    ->schema([
                        TextEntry::make('payment_reference')
                            ->label('Payment Reference')
                            ->copyable()
                            ->size(TextSize::Large)
                            ->weight('bold'),
                        
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn ($record) => $record->statusColor)
                            ->formatStateUsing(fn ($record) => $record->statusText)
                            ->size(TextSize::Large),
                        
                        TextEntry::make('created_at')
                            ->label('Uploaded On')
                            ->dateTime('M d, Y H:i')
                            ->helperText(fn ($record) => $record->created_at->diffForHumans()),
                        
                        TextEntry::make('verified_at')
                            ->label('Verified On')
                            ->dateTime('M d, Y H:i')
                            ->placeholder('Not yet verified')
                            ->helperText(fn ($record) => $record->verified_at?->diffForHumans())
                            ->visible(fn ($record) => $record->verified_at),
                    ])
                    ->columns(2),

                Section::make('Payment Details')
                    ->schema([
                        TextEntry::make('student.user.full_name')
                            ->label('Child')
                            ->size(TextSize::Large),
                        
                        TextEntry::make('course.title')
                            ->label('Course')
                            ->state(fn ($record) => $record->course?->course_code),
                        
                        TextEntry::make('amount')
                            ->label('Amount Paid')
                            ->money(fn ($record) => $record->currency)
                            ->size(TextSize::Large)
                            ->weight('bold')
                            ->color('success'),
                        
                        TextEntry::make('payment_date')
                            ->label('Payment Date')
                            ->date('M d, Y')
                            ->helperText(fn ($record) => $record->payment_date->diffForHumans()),
                        
                        TextEntry::make('payment_method')
                            ->badge()
                            ->color('info')
                            ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state))),
                        
                        TextEntry::make('currency')
                            ->badge(),
                    ])
                    ->columns(3),

                Section::make('Payment Receipt')
                    ->schema([
                        ImageEntry::make('receipt_path')
                            ->label('Receipt Image')
                            ->disk('public')
                            ->imageHeight(400)
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record->receipt_path && in_array($record->receipt_path, ['jpg', 'jpeg', 'png', 'gif'])),
                        
                        TextEntry::make('receipt_download')
                            ->label('Download Receipt')
                            ->formatStateUsing(function ($record) {
                                if (!$record->receipt_path) return 'No receipt uploaded';
                                
                                $url = asset('storage/' . $record->receipt_path);
                                $filename = $record->receipt_filename ?? basename($record->receipt_path);
                                return "<a href='{$url}' target='_blank' class='text-primary-600 hover:underline'>📎 {$filename}</a>";
                            })
                            ->html()
                            ->columnSpanFull(),
                    ]),

                Section::make('Additional Notes')
                    ->schema([
                        TextEntry::make('parent_notes')
                            ->label('Your Notes')
                            ->columnSpanFull()
                            ->placeholder('No notes provided'),
                    ])
                    ->visible(fn ($record) => !empty($record->parent_notes))
                    ->collapsible(),

                Section::make('Verification Details')
                    ->schema([
                        TextEntry::make('verifier.full_name')
                            ->label('Verified By')
                            ->placeholder('Pending verification'),
                        
                        TextEntry::make('verified_at')
                            ->label('Verified On')
                            ->dateTime('M d, Y H:i')
                            ->placeholder('Pending'),
                        
                        TextEntry::make('admin_notes')
                            ->label('Admin Notes')
                            ->columnSpanFull()
                            ->placeholder('No notes')
                            ->color(fn ($record) => $record->status === 'rejected' ? 'danger' : 'gray'),
                    ])
                    ->visible(fn ($record) => $record->status !== 'pending')
                    ->columns(2),

                Section::make('Subscription Information')
                    ->schema([
                        IconEntry::make('has_subscription')
                            ->label('Subscription Created')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('gray')
                            ->getStateUsing(fn ($record) => $record->hasSubscription()),
                        
                        TextEntry::make('subscription.subscription_code')
                            ->label('Subscription Code')
                            ->copyable()
                            ->visible(fn ($record) => $record->hasSubscription()),
                        
                        TextEntry::make('subscription.status')
                            ->badge()
                            ->color(fn ($record) => $record->subscription?->statusColor ?? 'gray')
                            ->visible(fn ($record) => $record->hasSubscription()),
                        
                        TextEntry::make('subscription.start_date')
                            ->date('M d, Y')
                            ->visible(fn ($record) => $record->hasSubscription()),
                        
                        TextEntry::make('subscription.end_date')
                            ->date('M d, Y')
                            ->visible(fn ($record) => $record->hasSubscription()),
                    ])
                    ->visible(fn ($record) => $record->hasSubscription())
                    ->columns(3),
            ]);
    }
}
